<?php

namespace console\controllers;

use yii\console\Controller;
use yii\console\ExitCode;
use common\models\billing\Meter;
use common\models\billing\MeterAlarm;

/**
 * Syncs alarms from ThingsBoard REST API.
 * 
 * Requires TB_URL, TB_USERNAME, TB_PASSWORD in .env.
 *
 * Usage:
 *   php yii tb-alarm-sync              # sync alarms for all meters
 *   php yii tb-alarm-sync/index 72     # sync alarms from last 72 hours
 *   php yii tb-alarm-sync/devices      # sync unassigned devices from TB
 */
class TbAlarmSyncController extends Controller
{
    private $baseUrl;
    private $token;

    /**
     * Sync alarms from ThingsBoard for all meters
     */
    public function actionIndex($hours = 24)
    {
        $this->baseUrl = rtrim($_ENV['TB_URL'] ?? '', '/');
        $username = $_ENV['TB_USERNAME'] ?? null;
        $password = $_ENV['TB_PASSWORD'] ?? null;

        if (!$this->baseUrl || !$username || !$password) {
            $this->stderr("TB_URL, TB_USERNAME and TB_PASSWORD must be set in .env\n");
            return ExitCode::CONFIG;
        }

        // Login to ThingsBoard
        $login = $this->request('POST', '/api/auth/login', [
            'username' => $username,
            'password' => $password,
        ]);
        if (empty($login['token'])) {
            $this->stderr("ThingsBoard login failed\n");
            return ExitCode::UNAVAILABLE;
        }
        $this->token = $login['token'];

        $endTs = (int)(microtime(true) * 1000);
        $startTs = $endTs - ((int)$hours * 3600 * 1000);
        $imported = 0;
        $updated = 0;

        // Get all alarms from ThingsBoard
        $this->stdout("Fetching alarms from ThingsBoard (last {$hours} hours)...\n");
        
        $pageSize = 100;
        $page = 0;
        $hasMore = true;

        while ($hasMore) {
            $alarms = $this->request('GET', "/api/alarm/TENANT?pageSize={$pageSize}&page={$page}&sortProperty=createdTime&sortOrder=DESC&startTime={$startTs}&endTime={$endTs}", null, $this->token);
            
            if (empty($alarms['data'])) {
                $hasMore = false;
                continue;
            }

            foreach ($alarms['data'] as $alarm) {
                $result = $this->processAlarm($alarm);
                if ($result === 'imported') {
                    $imported++;
                } elseif ($result === 'updated') {
                    $updated++;
                }
            }

            $hasMore = $alarms['hasNext'] ?? false;
            $page++;
        }

        $this->stdout("Sync complete. {$imported} new alarm(s), {$updated} updated.\n");
        return ExitCode::OK;
    }

    /**
     * Sync unassigned devices from ThingsBoard (meters starting with 0025601)
     */
    public function actionDevices()
    {
        $this->baseUrl = rtrim($_ENV['TB_URL'] ?? '', '/');
        $username = $_ENV['TB_USERNAME'] ?? null;
        $password = $_ENV['TB_PASSWORD'] ?? null;

        if (!$this->baseUrl || !$username || !$password) {
            $this->stderr("TB_URL, TB_USERNAME and TB_PASSWORD must be set in .env\n");
            return ExitCode::CONFIG;
        }

        // Login to ThingsBoard
        $login = $this->request('POST', '/api/auth/login', [
            'username' => $username,
            'password' => $password,
        ]);
        if (empty($login['token'])) {
            $this->stderr("ThingsBoard login failed\n");
            return ExitCode::UNAVAILABLE;
        }
        $this->token = $login['token'];

        $this->stdout("Fetching devices from ThingsBoard...\n");
        
        $pageSize = 100;
        $page = 0;
        $hasMore = true;
        $imported = 0;

        while ($hasMore) {
            $devices = $this->request('GET', "/api/tenant/devices?pageSize={$pageSize}&page={$page}", null, $this->token);
            
            if (empty($devices['data'])) {
                $hasMore = false;
                continue;
            }

            foreach ($devices['data'] as $device) {
                $deviceName = $device['name'] ?? '';
                $deviceType = $device['type'] ?? 'unknown';
                
                // Check if meter exists
                $meter = Meter::findOne(['serial_number' => $deviceName]);
                if ($meter) {
                    continue; // Already exists
                }

                // Get device credentials
                $deviceId = $device['id']['id'] ?? null;
                if (!$deviceId) {
                    continue;
                }

                $credentials = $this->request('GET', "/api/device/{$deviceId}/credentials", null, $this->token);
                
                // Create new meter
                $meter = new Meter();
                $meter->serial_number = $deviceName;
                $meter->meter_type = $deviceType;
                $meter->dev_eui = $deviceName; // Use device name as dev_eui if not available
                $meter->app_eui = 'TB-' . substr($deviceId, 0, 16);
                $meter->app_key = $credentials['credentialsId'] ?? 'unknown';
                $meter->status = 1; // Active
                
                if ($meter->save()) {
                    $imported++;
                    $this->stdout("  Imported meter: {$deviceName} (type: {$deviceType})\n");
                } else {
                    $this->stderr("  Failed to import meter {$deviceName}: " . json_encode($meter->errors) . "\n");
                }
            }

            $hasMore = $devices['hasNext'] ?? false;
            $page++;
        }

        $this->stdout("Device sync complete. {$imported} new meter(s) imported.\n");
        return ExitCode::OK;
    }

    /**
     * Process a single alarm from ThingsBoard
     */
    private function processAlarm($alarm)
    {
        $tbAlarmId = $alarm['id']['id'] ?? null;
        if (!$tbAlarmId) {
            return null;
        }

        // Check if alarm already exists
        $existing = MeterAlarm::findOne(['tb_alarm_id' => $tbAlarmId]);
        
        // Get device info
        $originatorId = $alarm['originator']['id'] ?? null;
        $originatorType = $alarm['originator']['entityType'] ?? null;
        
        $devEui = '';
        $serialNumber = '';
        $meterId = null;

        if ($originatorType === 'DEVICE' && $originatorId) {
            // Get device details
            $device = $this->request('GET', "/api/device/{$originatorId}", null, $this->token);
            if ($device) {
                $devEui = $device['name'] ?? '';
                $serialNumber = $device['name'] ?? '';
                
                // Find meter in our database
                $meter = Meter::findOne(['serial_number' => $serialNumber]);
                if (!$meter) {
                    $meter = Meter::findOne(['dev_eui' => $devEui]);
                }
                if ($meter) {
                    $meterId = $meter->id;
                }
            }
        }

        // Map ThingsBoard alarm type to our types
        $alarmType = strtolower($alarm['type'] ?? 'unknown');
        $alarmType = str_replace(' ', '_', $alarmType);

        // Map severity
        $severity = match ($alarm['severity'] ?? 'WARNING') {
            'CRITICAL' => MeterAlarm::SEVERITY_CRITICAL,
            'MAJOR' => MeterAlarm::SEVERITY_CRITICAL,
            'MINOR' => MeterAlarm::SEVERITY_WARNING,
            'WARNING' => MeterAlarm::SEVERITY_WARNING,
            'INDETERMINATE' => MeterAlarm::SEVERITY_INFO,
            default => MeterAlarm::SEVERITY_WARNING,
        };

        // Map status
        $status = match ($alarm['status'] ?? 'ACTIVE_UNACK') {
            'ACTIVE_UNACK' => MeterAlarm::STATUS_ACTIVE,
            'ACTIVE_ACK' => MeterAlarm::STATUS_ACKNOWLEDGED,
            'CLEARED_UNACK' => MeterAlarm::STATUS_CLEARED,
            'CLEARED_ACK' => MeterAlarm::STATUS_CLEARED,
            default => MeterAlarm::STATUS_ACTIVE,
        };

        $originatedAt = isset($alarm['createdTime']) 
            ? date('Y-m-d H:i:s', (int)($alarm['createdTime'] / 1000))
            : date('Y-m-d H:i:s');

        $clearedAt = null;
        if (isset($alarm['clearTs']) && $alarm['clearTs'] > 0) {
            $clearedAt = date('Y-m-d H:i:s', (int)($alarm['clearTs'] / 1000));
        }

        $acknowledgedAt = null;
        if (isset($alarm['ackTs']) && $alarm['ackTs'] > 0) {
            $acknowledgedAt = date('Y-m-d H:i:s', (int)($alarm['ackTs'] / 1000));
        }

        if ($existing) {
            // Update existing alarm
            $existing->status = $status;
            $existing->cleared_at = $clearedAt;
            $existing->acknowledged_at = $acknowledgedAt;
            $existing->details = json_encode($alarm['details'] ?? []);
            $existing->save();
            return 'updated';
        }

        // Create new alarm
        $model = new MeterAlarm();
        $model->meter_id = $meterId;
        $model->dev_eui = $devEui ?: 'unknown';
        $model->serial_number = $serialNumber ?: null;
        $model->alarm_type = $alarmType;
        $model->severity = $severity;
        $model->status = $status;
        $model->message = $alarm['details']['message'] ?? ($alarm['type'] ?? 'Alarm');
        $model->details = json_encode($alarm['details'] ?? []);
        $model->tb_alarm_id = $tbAlarmId;
        $model->originated_at = $originatedAt;
        $model->cleared_at = $clearedAt;
        $model->acknowledged_at = $acknowledgedAt;

        if ($model->save()) {
            return 'imported';
        }

        $this->stderr("Failed to save alarm: " . json_encode($model->errors) . "\n");
        return null;
    }

    /**
     * Make HTTP request to ThingsBoard API
     */
    private function request($method, $path, $body = null, $token = null)
    {
        $ch = curl_init($this->baseUrl . $path);
        $headers = ['Content-Type: application/json'];
        if ($token) {
            $headers[] = 'X-Authorization: Bearer ' . $token;
        }
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 30,
        ]);
        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        }
        $response = curl_exec($ch);
        curl_close($ch);
        return $response ? (json_decode($response, true) ?: []) : [];
    }
}

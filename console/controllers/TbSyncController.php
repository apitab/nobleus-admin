<?php

namespace console\controllers;

use yii\console\Controller;
use yii\console\ExitCode;
use common\models\billing\Meter;
use common\models\billing\MeterAssignment;
use common\models\billing\MeterReadingRaw;

/**
 * Pulls telemetry from the ThingsBoard REST API. Primary ingestion is the
 * real-time webhook (api/v1/telemetry-receive); this command is a fallback
 * for backfill or an admin-triggered force sync.
 *
 * Requires TB_URL, TB_USERNAME, TB_PASSWORD in .env.
 * Optional: TB_TELEMETRY_KEY (default "reading").
 *
 * Usage:
 *   php yii tb-sync              # sync last 24h for all meters
 *   php yii tb-sync/index 72     # sync last 72 hours
 */
class TbSyncController extends Controller
{
    private $baseUrl;

    public function actionIndex($hours = 24)
    {
        $this->baseUrl = rtrim($_ENV['TB_URL'] ?? '', '/');
        $username = $_ENV['TB_USERNAME'] ?? null;
        $password = $_ENV['TB_PASSWORD'] ?? null;
        $key = $_ENV['TB_TELEMETRY_KEY'] ?? 'absoluteMeterReading_liters';

        if (!$this->baseUrl || !$username || !$password) {
            $this->stderr("TB_URL, TB_USERNAME and TB_PASSWORD must be set in .env\n");
            return ExitCode::CONFIG;
        }

        $login = $this->request('POST', '/api/auth/login', [
            'username' => $username,
            'password' => $password,
        ]);
        if (empty($login['token'])) {
            $this->stderr("ThingsBoard login failed\n");
            return ExitCode::UNAVAILABLE;
        }
        $token = $login['token'];

        $endTs = (int)(microtime(true) * 1000);
        $startTs = $endTs - ((int)$hours * 3600 * 1000);
        $imported = 0;

        foreach (Meter::find()->all() as $meter) {
            // TB devices are named by 8-digit meter serial; fall back to DevEUI
            $device = $this->request('GET', '/api/tenant/devices?deviceName=' . urlencode($meter->serial_number), null, $token);
            if (empty($device['id']['id'])) {
                $device = $this->request('GET', '/api/tenant/devices?deviceName=' . urlencode($meter->dev_eui), null, $token);
            }
            if (empty($device['id']['id'])) {
                $this->stdout("Skipping {$meter->serial_number}: device not found in TB\n");
                continue;
            }

            $ts = $this->request('GET', "/api/plugins/telemetry/DEVICE/{$device['id']['id']}/values/timeseries"
                . "?keys={$key}&startTs={$startTs}&endTs={$endTs}&limit=1000", null, $token);
            if (empty($ts[$key])) {
                continue;
            }

            $assignment = MeterAssignment::findOne(['meter_id' => $meter->id]);

            foreach ($ts[$key] as $point) {
                $readingTime = date('Y-m-d H:i:s', (int)($point['ts'] / 1000));
                $exists = MeterReadingRaw::find()
                    ->where(['dev_eui' => $meter->dev_eui, 'reading_time' => $readingTime])
                    ->exists();
                if ($exists || !is_numeric($point['value'])) {
                    continue;
                }

                $model = new MeterReadingRaw([
                    'dev_eui' => $meter->dev_eui,
                    'meter_id' => $meter->id,
                    'supply_no' => $assignment->supply_no ?? null,
                    'reading_value' => (float)$point['value'],
                    'reading_time' => $readingTime,
                    'payload' => json_encode($point),
                    'source' => 'rest-sync',
                    'status' => MeterReadingRaw::STATUS_PENDING,
                ]);
                if ($model->save()) {
                    $imported++;
                }
            }
        }

        $this->stdout("Sync complete. {$imported} new reading(s) stored.\n");
        return ExitCode::OK;
    }

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

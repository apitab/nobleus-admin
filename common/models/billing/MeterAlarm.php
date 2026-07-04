<?php

namespace common\models\billing;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * MeterAlarm model - stores alarm history from ThingsBoard
 *
 * @property int $id
 * @property int|null $meter_id
 * @property string $dev_eui
 * @property string|null $serial_number
 * @property string $alarm_type
 * @property string $severity
 * @property string $status
 * @property string|null $message
 * @property string|null $details
 * @property string|null $tb_alarm_id
 * @property string $originated_at
 * @property string|null $cleared_at
 * @property string|null $acknowledged_at
 * @property int|null $acknowledged_by
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Meter $meter
 */
class MeterAlarm extends ActiveRecord
{
    const STATUS_ACTIVE = 'ACTIVE';
    const STATUS_CLEARED = 'CLEARED';
    const STATUS_ACKNOWLEDGED = 'ACKNOWLEDGED';

    const SEVERITY_CRITICAL = 'CRITICAL';
    const SEVERITY_WARNING = 'WARNING';
    const SEVERITY_INFO = 'INFO';

    public static function tableName()
    {
        return '{{%meter_alarms}}';
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    public function rules()
    {
        return [
            [['dev_eui', 'alarm_type', 'originated_at'], 'required'],
            [['meter_id', 'acknowledged_by'], 'integer'],
            [['message', 'details'], 'string'],
            [['originated_at', 'cleared_at', 'acknowledged_at', 'created_at', 'updated_at'], 'safe'],
            [['dev_eui', 'serial_number', 'alarm_type', 'tb_alarm_id'], 'string', 'max' => 100],
            [['severity', 'status'], 'string', 'max' => 20],
            [['severity'], 'in', 'range' => [self::SEVERITY_CRITICAL, self::SEVERITY_WARNING, self::SEVERITY_INFO]],
            [['status'], 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_CLEARED, self::STATUS_ACKNOWLEDGED]],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'meter_id' => 'Meter',
            'dev_eui' => 'DevEUI',
            'serial_number' => 'Serial Number',
            'alarm_type' => 'Alarm Type',
            'severity' => 'Severity',
            'status' => 'Status',
            'message' => 'Message',
            'details' => 'Details',
            'tb_alarm_id' => 'TB Alarm ID',
            'originated_at' => 'Originated At',
            'cleared_at' => 'Cleared At',
            'acknowledged_at' => 'Acknowledged At',
            'acknowledged_by' => 'Acknowledged By',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function getMeter()
    {
        return $this->hasOne(Meter::class, ['id' => 'meter_id']);
    }

    public function getAcknowledgedByUser()
    {
        return $this->hasOne(\backend\models\Users::class, ['id' => 'acknowledged_by']);
    }

    /**
     * Get severity badge class for display
     */
    public function getSeverityBadgeClass()
    {
        return match ($this->severity) {
            self::SEVERITY_CRITICAL => 'danger',
            self::SEVERITY_WARNING => 'warning',
            self::SEVERITY_INFO => 'info',
            default => 'secondary',
        };
    }

    /**
     * Get status badge class for display
     */
    public function getStatusBadgeClass()
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => 'danger',
            self::STATUS_CLEARED => 'success',
            self::STATUS_ACKNOWLEDGED => 'info',
            default => 'secondary',
        };
    }

    /**
     * Get human-readable alarm type
     */
    public function getAlarmTypeLabel()
    {
        $labels = [
            'inactivity' => 'Device Inactive',
            'low_battery' => 'Low Battery',
            'tamper' => 'Tamper Detected',
            'leak' => 'Leak Detected',
            'high_consumption' => 'High Consumption',
            'reverse_flow' => 'Reverse Flow',
            'communication_error' => 'Communication Error',
            'sensor_error' => 'Sensor Error',
        ];
        return $labels[$this->alarm_type] ?? ucwords(str_replace('_', ' ', $this->alarm_type));
    }

    /**
     * Get all alarm types for filter dropdown
     */
    public static function getAlarmTypes()
    {
        return [
            'inactivity' => 'Device Inactive',
            'low_battery' => 'Low Battery',
            'tamper' => 'Tamper Detected',
            'leak' => 'Leak Detected',
            'high_consumption' => 'High Consumption',
            'reverse_flow' => 'Reverse Flow',
            'communication_error' => 'Communication Error',
            'sensor_error' => 'Sensor Error',
        ];
    }

    /**
     * Get all severities for filter dropdown
     */
    public static function getSeverities()
    {
        return [
            self::SEVERITY_CRITICAL => 'Critical',
            self::SEVERITY_WARNING => 'Warning',
            self::SEVERITY_INFO => 'Info',
        ];
    }

    /**
     * Get all statuses for filter dropdown
     */
    public static function getStatuses()
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_CLEARED => 'Cleared',
            self::STATUS_ACKNOWLEDGED => 'Acknowledged',
        ];
    }
}

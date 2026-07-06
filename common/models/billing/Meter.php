<?php

namespace common\models\billing;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * @property int $id
 * @property string $serial_number
 * @property string $meter_type
 * @property string|null $frame_type
 * @property string|null $diameter
 * @property string $dev_eui
 * @property string $app_eui
 * @property string $app_key
 * @property int $status
 * @property string|null $installed_at
 */
class Meter extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%meters}}';
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
            [['serial_number', 'meter_type', 'dev_eui', 'app_eui', 'app_key'], 'required'],
            [['status'], 'integer'],
            [['installed_at', 'frame_type', 'diameter'], 'safe'],
            [['serial_number', 'dev_eui', 'app_eui', 'app_key'], 'string', 'max' => 100],
            [['meter_type', 'frame_type', 'diameter'], 'string', 'max' => 50],
            [['serial_number', 'dev_eui'], 'unique'],
        ];
    }

    public function getAssignment()
    {
        return $this->hasOne(MeterAssignment::class, ['meter_id' => 'id']);
    }

    public function getReadings()
    {
        return $this->hasMany(MeterReadingRaw::class, ['meter_id' => 'id']);
    }

    public function getAlarms()
    {
        return $this->hasMany(MeterAlarm::class, ['meter_id' => 'id']);
    }

    public function getActiveAlarms()
    {
        return $this->hasMany(MeterAlarm::class, ['meter_id' => 'id'])
            ->where(['status' => MeterAlarm::STATUS_ACTIVE]);
    }

    /**
     * Get count of active alarms for this meter
     */
    public function getActiveAlarmCount()
    {
        return $this->getActiveAlarms()->count();
    }

    /**
     * Check if meter has any critical active alarms
     */
    public function hasCriticalAlarm()
    {
        return $this->getActiveAlarms()
            ->andWhere(['severity' => MeterAlarm::SEVERITY_CRITICAL])
            ->exists();
    }
}

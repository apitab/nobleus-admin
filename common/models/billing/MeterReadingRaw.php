<?php

namespace common\models\billing;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * @property int $id
 * @property string $dev_eui
 * @property int|null $meter_id
 * @property string|null $supply_no
 * @property string $reading_value
 * @property string $reading_time
 * @property string|null $payload
 * @property string $source
 * @property int $status
 */
class MeterReadingRaw extends ActiveRecord
{
    const STATUS_PENDING = 0;
    const STATUS_IMPORTED = 1;
    const STATUS_REJECTED = 2;

    public static function tableName()
    {
        return '{{%meter_readings_raw}}';
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
            [['dev_eui', 'reading_value', 'reading_time'], 'required'],
            [['meter_id', 'status'], 'integer'],
            [['reading_value'], 'number'],
            [['reading_time'], 'safe'],
            [['payload'], 'string'],
            [['dev_eui', 'supply_no'], 'string', 'max' => 100],
            [['source'], 'string', 'max' => 30],
        ];
    }

    public static function statusLabels()
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_IMPORTED => 'Posted',
            self::STATUS_REJECTED => 'Rejected',
        ];
    }

    public function getStatusLabel()
    {
        return self::statusLabels()[$this->status] ?? 'Unknown';
    }

    public function getMeter()
    {
        return $this->hasOne(Meter::class, ['id' => 'meter_id']);
    }

    public function getLedgerEntry()
    {
        return $this->hasOne(BillingLedger::class, ['raw_reading_id' => 'id']);
    }
}

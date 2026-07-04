<?php

namespace common\models\billing;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * @property int $id
 * @property int $raw_reading_id
 * @property int|null $meter_id
 * @property string $supply_no
 * @property string|null $previous_reading
 * @property string $current_reading
 * @property string|null $consumption
 * @property string $reading_date
 * @property int $imported_by
 * @property string $imported_at
 */
class BillingLedger extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%billing_ledger}}';
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
            [['raw_reading_id', 'supply_no', 'current_reading', 'reading_date', 'imported_by', 'imported_at'], 'required'],
            [['raw_reading_id', 'meter_id', 'imported_by'], 'integer'],
            [['previous_reading', 'current_reading', 'consumption'], 'number'],
            [['reading_date', 'imported_at'], 'safe'],
            [['supply_no'], 'string', 'max' => 100],
            [['raw_reading_id'], 'unique'],
        ];
    }

    public function getRawReading()
    {
        return $this->hasOne(MeterReadingRaw::class, ['id' => 'raw_reading_id']);
    }

    public function getMeter()
    {
        return $this->hasOne(Meter::class, ['id' => 'meter_id']);
    }

    public function getImportedByUser()
    {
        return $this->hasOne(\backend\models\Users::class, ['user_id' => 'imported_by']);
    }
}

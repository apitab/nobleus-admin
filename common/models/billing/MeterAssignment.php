<?php

namespace common\models\billing;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * @property int $id
 * @property int $meter_id
 * @property string $supply_no
 * @property string|null $customer_phone
 * @property string|null $receipt_no
 * @property int $assigned_by
 * @property string $assigned_at
 */
class MeterAssignment extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%meter_assignments}}';
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
            [['meter_id', 'supply_no', 'assigned_by', 'assigned_at'], 'required'],
            [['meter_id', 'assigned_by'], 'integer'],
            [['assigned_at'], 'safe'],
            [['supply_no', 'receipt_no'], 'string', 'max' => 100],
            [['customer_phone'], 'string', 'max' => 50],
            [['meter_id'], 'unique'],
        ];
    }

    public function getMeter()
    {
        return $this->hasOne(Meter::class, ['id' => 'meter_id']);
    }

    public function getCustomer()
    {
        return $this->hasOne(\backend\models\Customers::class, ['phone_number' => 'customer_phone']);
    }
}

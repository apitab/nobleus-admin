<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "vendor_deliveries".
 *
 * @property int $id
 * @property int $cr_id
 * @property float $start_lat
 * @property float $start_lng
 * @property float $end_lat
 * @property float $end_lng
 * @property float|null $distance_km
 * @property float|null $points_earned
 * @property string $delivery_date
 * @property string|null $created_at
 * @property int|null $status
 *
 * @property CustomerRequests $cr
 */
class VendorDeliveries extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'vendor_deliveries';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['distance_km', 'points_earned'], 'default', 'value' => null],
            [['status'], 'default', 'value' => 0],
            [['cr_id', 'start_lat', 'start_lng', 'end_lat', 'end_lng', 'delivery_date'], 'required'],
            [['cr_id', 'status'], 'integer'],
            [['start_lat', 'start_lng', 'end_lat', 'end_lng', 'distance_km', 'points_earned'], 'number'],
            [['delivery_date', 'created_at'], 'safe'],
            [['cr_id'], 'exist', 'skipOnError' => true, 'targetClass' => CustomerRequests::class, 'targetAttribute' => ['cr_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'cr_id' => Yii::t('app', 'Cr ID'),
            'start_lat' => Yii::t('app', 'Start Lat'),
            'start_lng' => Yii::t('app', 'Start Lng'),
            'end_lat' => Yii::t('app', 'End Lat'),
            'end_lng' => Yii::t('app', 'End Lng'),
            'distance_km' => Yii::t('app', 'Distance Km'),
            'points_earned' => Yii::t('app', 'Points Earned'),
            'delivery_date' => Yii::t('app', 'Delivery Date'),
            'created_at' => Yii::t('app', 'Created At'),
            'status' => Yii::t('app', 'Status'),
        ];
    }

    /**
     * Gets query for [[Cr]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCr()
    {
        return $this->hasOne(CustomerRequests::class, ['id' => 'cr_id']);
    }

}

<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "ratings".
 *
 * @property int $id
 * @property int $vendor_id
 * @property int $rating
 * @property string|null $notes
 * @property int $order_id
 * @property int $status
 * @property string $date_created
 * @property string|null $date_modified
 *
 * @property CustomerRequests $order
 * @property Vendors $vendor
 */
class Ratings extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ratings';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['vendor_id', 'rating', 'order_id', 'status', 'date_created'], 'required'],
            [['vendor_id', 'rating', 'order_id', 'status'], 'integer'],
            [['date_created', 'date_modified'], 'safe'],
            [['notes'], 'string', 'max' => 250],
            [['order_id'], 'exist', 'skipOnError' => true, 'targetClass' => CustomerRequests::class, 'targetAttribute' => ['order_id' => 'id']],
            [['vendor_id'], 'exist', 'skipOnError' => true, 'targetClass' => Vendors::class, 'targetAttribute' => ['vendor_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'vendor_id' => Yii::t('app', 'Vendor ID'),
            'rating' => Yii::t('app', 'Rating'),
            'notes' => Yii::t('app', 'Notes'),
            'order_id' => Yii::t('app', 'Order ID'),
            'status' => Yii::t('app', 'Status'),
            'date_created' => Yii::t('app', 'Date Created'),
            'date_modified' => Yii::t('app', 'Date Modified'),
        ];
    }

    /**
     * Gets query for [[Order]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrder()
    {
        return $this->hasOne(CustomerRequests::class, ['id' => 'order_id']);
    }

    /**
     * Gets query for [[Vendor]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVendor()
    {
        return $this->hasOne(Vendors::class, ['id' => 'vendor_id']);
    }
}

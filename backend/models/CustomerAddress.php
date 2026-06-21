<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "customer_address".
 *
 * @property int $id
 * @property int $customer_id
 * @property int $location_id
 * @property string $address
 * @property string $location_coordinates
 * @property int $status
 * @property string $date_created
 * @property string|null $date_modified
 *
 * @property Customers $customer
 * @property Customers[] $customers
 * @property Locations $location
 */
class CustomerAddress extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'customer_address';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['customer_id', 'location_id', 'address', 'location_coordinates', 'status', 'date_created'], 'required'],
            [['customer_id', 'location_id', 'status'], 'integer'],
            [['date_created', 'date_modified'], 'safe'],
            [['address'], 'string', 'max' => 250],
            [['location_coordinates'], 'string', 'max' => 100],
            [['customer_id'], 'exist', 'skipOnError' => true, 'targetClass' => Customers::class, 'targetAttribute' => ['customer_id' => 'id']],
            [['location_id'], 'exist', 'skipOnError' => true, 'targetClass' => Locations::class, 'targetAttribute' => ['location_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'customer_id' => Yii::t('app','Customer'),
            'location_id' => Yii::t('app','Location'),
            'address' => Yii::t('app','Address'),
            'location_coordinates' => Yii::t('app','Location Coordinates'),
            'status' => Yii::t('app','Status'),
            'date_created' => Yii::t('app','Date Created'),
            'date_modified' => Yii::t('app','Date Modified'),
        ];
    }

    /**
     * Gets query for [[Customer]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCustomer()
    {
        return $this->hasOne(Customers::class, ['id' => 'customer_id']);
    }

    /**
     * Gets query for [[Customers]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCustomers()
    {
        return $this->hasMany(Customers::class, ['primary_address' => 'id']);
    }

    /**
     * Gets query for [[Location]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getLocation()
    {
        return $this->hasOne(Locations::class, ['id' => 'location_id']);
    }
}

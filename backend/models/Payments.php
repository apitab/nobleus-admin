<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "payments".
 *
 * @property int $id
 * @property int $request_id
 * @property float $amount
 * @property string $description
 * @property string|null $phone_number
 * @property int $status
 * @property int $payment_method_id
 * @property string $date_created
 * @property string|null $date_modified
 *
 * @property CustomerRequests[] $customerRequests
 * @property PaymentMethods $paymentMethod
 * @property PaymentStatus[] $paymentStatuses
 * @property CustomerRequests $request
 */
class Payments extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'payments';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['request_id', 'amount', 'description', 'status', 'payment_method_id', 'date_created'], 'required'],
            [['request_id', 'status', 'payment_method_id'], 'integer'],
            [['amount'], 'number'],
            [['date_created', 'date_modified'], 'safe'],
            [['description'], 'string', 'max' => 100],
            [['phone_number'], 'string', 'max' => 20],
            [['payment_method_id'], 'exist', 'skipOnError' => true, 'targetClass' => PaymentMethods::class, 'targetAttribute' => ['payment_method_id' => 'id']],
            [['request_id'], 'exist', 'skipOnError' => true, 'targetClass' => CustomerRequests::class, 'targetAttribute' => ['request_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'request_id' => Yii::t('app', 'Request ID'),
            'amount' => Yii::t('app', 'Amount'),
            'description' => Yii::t('app', 'Description'),
            'phone_number' => Yii::t('app', 'Phone Number'),
            'status' => Yii::t('app', 'Status'),
            'payment_method_id' => Yii::t('app', 'Payment Method ID'),
            'date_created' => Yii::t('app', 'Date Created'),
            'date_modified' => Yii::t('app', 'Date Modified'),
        ];
    }

    /**
     * Gets query for [[CustomerRequests]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCustomerRequests()
    {
        return $this->hasMany(CustomerRequests::class, ['payment_id' => 'id']);
    }

    /**
     * Gets query for [[PaymentMethod]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPaymentMethod()
    {
        return $this->hasOne(PaymentMethods::class, ['id' => 'payment_method_id']);
    }

    /**
     * Gets query for [[PaymentStatuses]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPaymentStatuses()
    {
        return $this->hasMany(PaymentStatus::class, ['payment_id' => 'id']);
    }

    /**
     * Gets query for [[Request]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRequest()
    {
        return $this->hasOne(CustomerRequests::class, ['id' => 'request_id']);
    }
}

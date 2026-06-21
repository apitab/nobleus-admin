<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "invoices".
 *
 * @property int $id
 * @property int $customer_request_id
 * @property float $amount
 * @property string $edahab_number
 * @property string|null $currency
 * @property string|null $status
 * @property string|null $transaction_id
 * @property string|null $description
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property CustomerRequests $customerRequest
 */
class Invoices extends \yii\db\ActiveRecord
{

    /**
     * ENUM field values
     */
    const STATUS_PENDING = 'pending';
    const STATUS_SENT = 'sent';
    const STATUS_FAILED = 'failed';
    const STATUS_PAID = 'paid';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'invoices';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['transaction_id', 'description'], 'default', 'value' => null],
            [['currency'], 'default', 'value' => 'SLSH'],
            [['status'], 'default', 'value' => 'pending'],
            [['customer_request_id', 'amount', 'edahab_number'], 'required'],
            [['customer_request_id'], 'integer'],
            [['amount'], 'number'],
            [['status', 'description'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['edahab_number'], 'string', 'max' => 20],
            [['currency'], 'string', 'max' => 4],
            [['transaction_id'], 'string', 'max' => 255],
            ['status', 'in', 'range' => array_keys(self::optsStatus())],
            [['customer_request_id'], 'exist', 'skipOnError' => true, 'targetClass' => CustomerRequests::class, 'targetAttribute' => ['customer_request_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'customer_request_id' => Yii::t('app', 'Customer Request ID'),
            'amount' => Yii::t('app', 'Amount'),
            'edahab_number' => Yii::t('app', 'Edahab Number'),
            'currency' => Yii::t('app', 'Currency'),
            'status' => Yii::t('app', 'Status'),
            'transaction_id' => Yii::t('app', 'Transaction ID'),
            'description' => Yii::t('app', 'Description'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * Gets query for [[CustomerRequest]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCustomerRequest()
    {
        return $this->hasOne(CustomerRequests::class, ['id' => 'customer_request_id']);
    }


    /**
     * column status ENUM value labels
     * @return string[]
     */
    public static function optsStatus()
    {
        return [
            self::STATUS_PENDING => Yii::t('app', 'pending'),
            self::STATUS_SENT => Yii::t('app', 'sent'),
            self::STATUS_FAILED => Yii::t('app', 'failed'),
            self::STATUS_PAID => Yii::t('app', 'paid'),
            self::STATUS_CANCELLED => Yii::t('app', 'cancelled'),
        ];
    }

    /**
     * @return string
     */
    public function displayStatus()
    {
        return self::optsStatus()[$this->status];
    }

    /**
     * @return bool
     */
    public function isStatusPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function setStatusToPending()
    {
        $this->status = self::STATUS_PENDING;
    }

    /**
     * @return bool
     */
    public function isStatusSent()
    {
        return $this->status === self::STATUS_SENT;
    }

    public function setStatusToSent()
    {
        $this->status = self::STATUS_SENT;
    }

    /**
     * @return bool
     */
    public function isStatusFailed()
    {
        return $this->status === self::STATUS_FAILED;
    }

    public function setStatusToFailed()
    {
        $this->status = self::STATUS_FAILED;
    }

    /**
     * @return bool
     */
    public function isStatusPaid()
    {
        return $this->status === self::STATUS_PAID;
    }

    public function setStatusToPaid()
    {
        $this->status = self::STATUS_PAID;
    }

    /**
     * @return bool
     */
    public function isStatusCancelled()
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function setStatusToCancelled()
    {
        $this->status = self::STATUS_CANCELLED;
    }
}

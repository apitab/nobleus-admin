<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "withdrawals".
 *
 * @property int $id
 * @property string $from_type
 * @property int $from_id
 * @property float $amount
 * @property string|null $status
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class Withdrawals extends \yii\db\ActiveRecord
{

    /**
     * ENUM field values
     */
    const FROM_TYPE_CUSTOMER = 'customer';
    const FROM_TYPE_VENDOR = 'vendor';
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_SUCCESS = 'success';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'withdrawals';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_at'], 'default', 'value' => null],
            [['status'], 'default', 'value' => 'pending'],
            [['from_type', 'from_id', 'amount'], 'required'],
            [['from_type', 'status'], 'string'],
            [['from_id'], 'integer'],
            [['amount'], 'number'],
            [['created_at', 'updated_at'], 'safe'],
            ['from_type', 'in', 'range' => array_keys(self::optsFromType())],
            ['status', 'in', 'range' => array_keys(self::optsStatus())],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'from_type' => Yii::t('app', 'From Type'),
            'from_id' => Yii::t('app', 'From ID'),
            'amount' => Yii::t('app', 'Amount'),
            'status' => Yii::t('app', 'Status'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }


    /**
     * column from_type ENUM value labels
     * @return string[]
     */
    public static function optsFromType()
    {
        return [
            self::FROM_TYPE_CUSTOMER => Yii::t('app', 'customer'),
            self::FROM_TYPE_VENDOR => Yii::t('app', 'vendor'),
        ];
    }

    /**
     * column status ENUM value labels
     * @return string[]
     */
    public static function optsStatus()
    {
        return [
            self::STATUS_PENDING => Yii::t('app', 'pending'),
            self::STATUS_PROCESSING => Yii::t('app', 'processing'),
            self::STATUS_SUCCESS => Yii::t('app', 'success'),
            self::STATUS_FAILED => Yii::t('app', 'failed'),
            self::STATUS_CANCELLED => Yii::t('app', 'cancelled'),
        ];
    }

    /**
     * @return string
     */
    public function displayFromType()
    {
        return self::optsFromType()[$this->from_type];
    }

    /**
     * @return bool
     */
    public function isFromTypeCustomer()
    {
        return $this->from_type === self::FROM_TYPE_CUSTOMER;
    }

    public function setFromTypeToCustomer()
    {
        $this->from_type = self::FROM_TYPE_CUSTOMER;
    }

    /**
     * @return bool
     */
    public function isFromTypeVendor()
    {
        return $this->from_type === self::FROM_TYPE_VENDOR;
    }

    public function setFromTypeToVendor()
    {
        $this->from_type = self::FROM_TYPE_VENDOR;
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
    public function isStatusProcessing()
    {
        return $this->status === self::STATUS_PROCESSING;
    }

    public function setStatusToProcessing()
    {
        $this->status = self::STATUS_PROCESSING;
    }

    /**
     * @return bool
     */
    public function isStatusSuccess()
    {
        return $this->status === self::STATUS_SUCCESS;
    }

    public function setStatusToSuccess()
    {
        $this->status = self::STATUS_SUCCESS;
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
    public function isStatusCancelled()
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function setStatusToCancelled()
    {
        $this->status = self::STATUS_CANCELLED;
    }

    /**
     * Gets query for [[Vendor]]
     */
    public function getVendor()
    {
        return $this->hasOne(Vendors::class, ['id' => 'from_id']);
    }

    /**
     * Gets query for [[Customer]]
     */
    public function getCustomer()
    {
        return $this->hasOne(Customers::class, ['id' => 'from_id']);
    }
}

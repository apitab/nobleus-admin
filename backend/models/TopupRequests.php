<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "topup_requests".
 *
 * @property int $id
 * @property string $phone_number
 * @property float $amount
 * @property string $status
 * @property string $date_created
 * @property string|null $date_modified
 * @property int $customer_id
 *
 * @property Customers $customer
 */
class TopupRequests extends \yii\db\ActiveRecord
{

    /**
     * ENUM field values
     */
    const STATUS_NEW = 'new';
    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELED = 'canceled';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'topup_requests';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['phone_number', 'amount', 'status', 'date_created', 'customer_id'], 'required'],
            [['amount'], 'number'],
            [['status'], 'string'],
            [['date_created', 'date_modified'], 'safe'],
            [['customer_id'], 'integer'],
            [['phone_number'], 'string', 'max' => 20],
            ['status', 'in', 'range' => array_keys(self::optsStatus())],
            [['customer_id'], 'exist', 'skipOnError' => true, 'targetClass' => Customers::class, 'targetAttribute' => ['customer_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'phone_number' => 'Phone Number',
            'amount' => 'Amount',
            'status' => 'Status',
            'date_created' => 'Date Created',
            'date_modified' => 'Date Modified',
            'customer_id' => 'Customer ID',
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
     * column status ENUM value labels
     * @return string[]
     */
    public static function optsStatus()
    {
        return [
            self::STATUS_NEW => 'new',
            self::STATUS_PENDING => 'pending',
            self::STATUS_COMPLETED => 'completed',
            self::STATUS_CANCELED => 'canceled',
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
    public function isStatusNew()
    {
        return $this->status === self::STATUS_NEW;
    }

    public function setStatusToNew()
    {
        $this->status = self::STATUS_NEW;
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
    public function isStatusCompleted()
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function setStatusToCompleted()
    {
        $this->status = self::STATUS_COMPLETED;
    }

    /**
     * @return bool
     */
    public function isStatusCanceled()
    {
        return $this->status === self::STATUS_CANCELED;
    }

    public function setStatusToCanceled()
    {
        $this->status = self::STATUS_CANCELED;
    }
}

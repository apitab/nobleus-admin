<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "notification".
 *
 * @property int $id
 * @property string $device_id
 * @property int $customer_id
 * @property string|null $data_values
 * @property string|null $notif_keys
 * @property string|null $notif_values
 * @property string $title
 * @property string|null $message
 * @property string|null $type
 * @property int $status
 * @property int $is_read
 * @property int $is_pushed
 * @property string $date_created
 * @property string|null $date_modified
 *
 * @property Customers $customer
 */
class Notification extends \yii\db\ActiveRecord
{

    /**
     * ENUM field values
     */
    const TYPE_ORDER = 'order';
    const TYPE_PAYMENT = 'payment';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'notification';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['data_values', 'notif_keys', 'notif_values', 'message', 'type'], 'default', 'value' => null],
            [['is_pushed'], 'default', 'value' => 0],
            [['device_id', 'customer_id', 'title', 'date_created'], 'required'],
            [['customer_id', 'status', 'is_read', 'is_pushed'], 'integer'],
            [['data_values', 'notif_keys', 'notif_values', 'message', 'type'], 'string'],
            [['date_created', 'date_modified'], 'safe'],
            [['device_id'], 'string', 'max' => 200],
            [['title'], 'string', 'max' => 100],
            ['type', 'in', 'range' => array_keys(self::optsType())],
            [['customer_id'], 'exist', 'skipOnError' => true, 'targetClass' => Customers::class, 'targetAttribute' => ['customer_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'device_id' => Yii::t('app', 'Device ID'),
            'customer_id' => Yii::t('app', 'Customer ID'),
            'data_values' => Yii::t('app', 'Data Values'),
            'notif_keys' => Yii::t('app', 'Notif Keys'),
            'notif_values' => Yii::t('app', 'Notif Values'),
            'title' => Yii::t('app', 'Title'),
            'message' => Yii::t('app', 'Message'),
            'type' => Yii::t('app', 'Type'),
            'status' => Yii::t('app', 'Status'),
            'is_read' => Yii::t('app', 'Is Read'),
            'is_pushed' => Yii::t('app', 'Is Pushed'),
            'date_created' => Yii::t('app', 'Date Created'),
            'date_modified' => Yii::t('app', 'Date Modified'),
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
     * column type ENUM value labels
     * @return string[]
     */
    public static function optsType()
    {
        return [
            self::TYPE_ORDER => Yii::t('app', 'order'),
            self::TYPE_PAYMENT => Yii::t('app', 'payment'),
        ];
    }

    /**
     * @return string
     */
    public function displayType()
    {
        return self::optsType()[$this->type];
    }

    /**
     * @return bool
     */
    public function isTypeOrder()
    {
        return $this->type === self::TYPE_ORDER;
    }

    public function setTypeToOrder()
    {
        $this->type = self::TYPE_ORDER;
    }

    /**
     * @return bool
     */
    public function isTypePayment()
    {
        return $this->type === self::TYPE_PAYMENT;
    }

    public function setTypeToPayment()
    {
        $this->type = self::TYPE_PAYMENT;
    }
}

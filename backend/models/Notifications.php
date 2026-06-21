<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "notifications".
 *
 * @property int $id
 * @property string $device_id
 * @property string|null $data_values
 * @property string $key_type
 * @property int $key_id
 * @property string|null $type
 * @property string $title
 * @property string $message
 * @property int $status
 * @property int|null $is_read
 * @property string $date_created
 * @property string|null $date_modified
 * @property string|null $not_type
 */
class Notifications extends \yii\db\ActiveRecord
{

    /**
     * ENUM field values
     */
    const KEY_TYPE_CUSTOMER = 'customer';
    const KEY_TYPE_VENDOR = 'vendor';
    const NOT_TYPE_SUCCESS = 'success';
    const NOT_TYPE_FAILED = 'failed';
    const NOT_TYPE_ALERT = 'alert';
    const NOT_TYPE_WARNING = 'warning';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'notifications';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['data_values', 'type', 'not_type'], 'default', 'value' => null],
            [['is_read'], 'default', 'value' => 0],
            [['device_id', 'key_type', 'key_id', 'title', 'message', 'date_created'], 'required'],
            [['key_type', 'message', 'not_type'], 'string'],
            [['key_id', 'status', 'is_read'], 'integer'],
            [['date_created', 'date_modified'], 'safe'],
            [['device_id', 'data_values'], 'string', 'max' => 250],
            [['type'], 'string', 'max' => 20],
            [['title'], 'string', 'max' => 100],
            ['key_type', 'in', 'range' => array_keys(self::optsKeyType())],
            ['not_type', 'in', 'range' => array_keys(self::optsNotType())],
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
            'data_values' => Yii::t('app', 'Data Values'),
            'key_type' => Yii::t('app', 'Key Type'),
            'key_id' => Yii::t('app', 'Key ID'),
            'type' => Yii::t('app', 'Type'),
            'title' => Yii::t('app', 'Title'),
            'message' => Yii::t('app', 'Message'),
            'status' => Yii::t('app', 'Status'),
            'is_read' => Yii::t('app', 'Is Read'),
            'date_created' => Yii::t('app', 'Date Created'),
            'date_modified' => Yii::t('app', 'Date Modified'),
            'not_type' => Yii::t('app', 'Not Type'),
        ];
    }


    /**
     * column key_type ENUM value labels
     * @return string[]
     */
    public static function optsKeyType()
    {
        return [
            self::KEY_TYPE_CUSTOMER => Yii::t('app', 'customer'),
            self::KEY_TYPE_VENDOR => Yii::t('app', 'vendor'),
        ];
    }

    /**
     * column not_type ENUM value labels
     * @return string[]
     */
    public static function optsNotType()
    {
        return [
            self::NOT_TYPE_SUCCESS => Yii::t('app', 'success'),
            self::NOT_TYPE_FAILED => Yii::t('app', 'failed'),
            self::NOT_TYPE_ALERT => Yii::t('app', 'alert'),
            self::NOT_TYPE_WARNING => Yii::t('app', 'warning'),
        ];
    }

    /**
     * @return string
     */
    public function displayKeyType()
    {
        return self::optsKeyType()[$this->key_type];
    }

    /**
     * @return bool
     */
    public function isKeyTypeCustomer()
    {
        return $this->key_type === self::KEY_TYPE_CUSTOMER;
    }

    public function setKeyTypeToCustomer()
    {
        $this->key_type = self::KEY_TYPE_CUSTOMER;
    }

    /**
     * @return bool
     */
    public function isKeyTypeVendor()
    {
        return $this->key_type === self::KEY_TYPE_VENDOR;
    }

    public function setKeyTypeToVendor()
    {
        $this->key_type = self::KEY_TYPE_VENDOR;
    }

    /**
     * @return string
     */
    public function displayNotType()
    {
        return self::optsNotType()[$this->not_type];
    }

    /**
     * @return bool
     */
    public function isNotTypeSuccess()
    {
        return $this->not_type === self::NOT_TYPE_SUCCESS;
    }

    public function setNotTypeToSuccess()
    {
        $this->not_type = self::NOT_TYPE_SUCCESS;
    }

    /**
     * @return bool
     */
    public function isNotTypeFailed()
    {
        return $this->not_type === self::NOT_TYPE_FAILED;
    }

    public function setNotTypeToFailed()
    {
        $this->not_type = self::NOT_TYPE_FAILED;
    }

    /**
     * @return bool
     */
    public function isNotTypeAlert()
    {
        return $this->not_type === self::NOT_TYPE_ALERT;
    }

    public function setNotTypeToAlert()
    {
        $this->not_type = self::NOT_TYPE_ALERT;
    }

    /**
     * @return bool
     */
    public function isNotTypeWarning()
    {
        return $this->not_type === self::NOT_TYPE_WARNING;
    }

    public function setNotTypeToWarning()
    {
        $this->not_type = self::NOT_TYPE_WARNING;
    }
}

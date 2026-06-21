<?php

namespace backend\models;

use Yii;
use backend\models\Vendors;

/**
 * This is the model class for table "complaints".
 *
 * @property int $id
 * @property string $type
 * @property int $customer_id
 * @property int|null $order_id
 * @property string $title
 * @property string|null $category
 * @property string $description
 * @property string|null $resolution_notes
 * @property int $status
 * @property string $date_created
 * @property string|null $date_modified
 *
 * @property Customers $customer
 * @property Customers $customer0
 * @property CustomerRequests $order
 */
class Complaints extends \yii\db\ActiveRecord
{

    /**
     * ENUM field values
     */
    const TYPE_VENDORS = 'vendors';
    const TYPE_CUSTOMERS = 'customers';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'complaints';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['order_id', 'category', 'resolution_notes'], 'default', 'value' => null],
            [['status'], 'default', 'value' => 0],
            [['type', 'customer_id', 'title', 'description', 'date_created'], 'required'],
            [['type', 'resolution_notes'], 'string'],
            [['customer_id', 'order_id', 'status'], 'integer'],
            [['date_created', 'date_modified'], 'safe'],
            [['title', 'description'], 'string', 'max' => 250],
            [['category'], 'string', 'max' => 100],
            ['type', 'in', 'range' => array_keys(self::optsType())],
            ['type', 'required'], // Ensure type cannot be null
            [['customer_id'], 'exist', 'skipOnError' => true, 'targetClass' => Customers::class, 'targetAttribute' => ['customer_id' => 'id'], 'when' => function($model) {
                return $model->type === self::TYPE_CUSTOMERS;
            }],
            [['customer_id'], 'exist', 'skipOnError' => true, 'targetClass' => Vendors::class, 'targetAttribute' => ['customer_id' => 'id'], 'when' => function($model) {
                return $model->type === self::TYPE_VENDORS;
            }],
            [['order_id'], 'exist', 'skipOnError' => true, 'targetClass' => CustomerRequests::class, 'targetAttribute' => ['order_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'type' => Yii::t('app', 'Type'),
            'customer_id' => Yii::t('app', 'Customer ID'),
            'order_id' => Yii::t('app', 'Order ID'),
            'title' => Yii::t('app', 'Title'),
            'category' => Yii::t('app', 'Category'),
            'description' => Yii::t('app', 'Description'),
            'resolution_notes' => Yii::t('app', 'Resolution Notes'),
            'status' => Yii::t('app', 'Status'),
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
     * Gets query for [[Customer0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCustomer0()
    {
        return $this->hasOne(Customers::class, ['id' => 'customer_id']);
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
     * Note: customer_id field is used as vendor_id when type is 'vendors'
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVendor()
    {
        return $this->hasOne(Vendors::class, ['id' => 'customer_id']);
    }


    /**
     * column type ENUM value labels
     * @return string[]
     */
    public static function optsType()
    {
        return [
            self::TYPE_VENDORS => Yii::t('app', 'Vendors'),
            self::TYPE_CUSTOMERS => Yii::t('app', 'Customers'),
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
    public function isTypeVendors()
    {
        return $this->type === self::TYPE_VENDORS;
    }

    public function setTypeToVendors()
    {
        $this->type = self::TYPE_VENDORS;
    }

    /**
     * @return bool
     */
    public function isTypeCustomers()
    {
        return $this->type === self::TYPE_CUSTOMERS;
    }

    public function setTypeToCustomers()
    {
        $this->type = self::TYPE_CUSTOMERS;
    }
}

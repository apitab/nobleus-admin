<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "faqs".
 *
 * @property int $id
 * @property string $target
 * @property string $type
 * @property string $title
 * @property string $description
 * @property int $status
 * @property string $date_created
 * @property string|null $date_modified
 */
class Faqs extends \yii\db\ActiveRecord
{

    /**
     * ENUM field values
     */
    const TARGET_VENDORS = 'vendors';
    const TARGET_CUSTOMERS = 'customers';
    const TYPE_FAQ = 'faq';
    const TYPE_GUIDE = 'guide';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'faqs';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['target', 'type', 'title', 'description', 'status', 'date_created'], 'required'],
            [['target', 'type', 'description'], 'string'],
            [['status'], 'integer'],
            [['date_created', 'date_modified'], 'safe'],
            [['title'], 'string', 'max' => 100],
            ['target', 'in', 'range' => array_keys(self::optsTarget())],
            ['type', 'in', 'range' => array_keys(self::optsType())],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'target' => Yii::t('app', 'Target'),
            'type' => Yii::t('app', 'Type'),
            'title' => Yii::t('app', 'Title'),
            'description' => Yii::t('app', 'Description'),
            'status' => Yii::t('app', 'Status'),
            'date_created' => Yii::t('app', 'Date Created'),
            'date_modified' => Yii::t('app', 'Date Modified'),
        ];
    }


    /**
     * column target ENUM value labels
     * @return string[]
     */
    public static function optsTarget()
    {
        return [
            self::TARGET_VENDORS => Yii::t('app', 'Vendors'),
            self::TARGET_CUSTOMERS => Yii::t('app', 'Customers'),
        ];
    }

    /**
     * column type ENUM value labels
     * @return string[]
     */
    public static function optsType()
    {
        return [
            self::TYPE_FAQ => 'faq',
            self::TYPE_GUIDE => 'guide',
        ];
    }

    /**
     * @return string
     */
    public function displayTarget()
    {
        return self::optsTarget()[$this->target];
    }

    /**
     * @return bool
     */
    public function isTargetVendors()
    {
        return $this->target === self::TARGET_VENDORS;
    }

    public function setTargetToVendors()
    {
        $this->target = self::TARGET_VENDORS;
    }

    /**
     * @return bool
     */
    public function isTargetCustomers()
    {
        return $this->target === self::TARGET_CUSTOMERS;
    }

    public function setTargetToCustomers()
    {
        $this->target = self::TARGET_CUSTOMERS;
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
    public function isTypeFaq()
    {
        return $this->type === self::TYPE_FAQ;
    }

    public function setTypeToFaq()
    {
        $this->type = self::TYPE_FAQ;
    }

    /**
     * @return bool
     */
    public function isTypeGuide()
    {
        return $this->type === self::TYPE_GUIDE;
    }

    public function setTypeToGuide()
    {
        $this->type = self::TYPE_GUIDE;
    }
}

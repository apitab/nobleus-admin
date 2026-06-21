<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "information_guides".
 *
 * @property int $id
 * @property string $target
 * @property string $title
 * @property string $description
 * @property string|null $type
 * @property int $status
 * @property string $date_created
 * @property string|null $date_modified
 */
class InformationGuides extends \yii\db\ActiveRecord
{

    /**
     * ENUM field values
     */
    const TARGET_VENDORS = 'vendors';
    const TARGET_CUSTOMERS = 'customers';
    const TYPE_GUIDE = 'guide';
    const TYPE_ARTICLE = 'article';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'information_guides';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['type'], 'default', 'value' => null],
            [['target', 'title', 'description', 'status', 'date_created'], 'required'],
            [['target', 'description', 'type'], 'string'],
            [['status'], 'integer'],
            [['date_created', 'date_modified'], 'safe'],
            [['title'], 'string', 'max' => 200],
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
            'id' => 'ID',
            'target' => 'Target',
            'title' => 'Title',
            'description' => 'Description',
            'type' => 'Type',
            'status' => 'Status',
            'date_created' => 'Date Created',
            'date_modified' => 'Date Modified',
        ];
    }


    /**
     * column target ENUM value labels
     * @return string[]
     */
    public static function optsTarget()
    {
        return [
            self::TARGET_VENDORS => 'vendors',
            self::TARGET_CUSTOMERS => 'customers',
        ];
    }

    /**
     * column type ENUM value labels
     * @return string[]
     */
    public static function optsType()
    {
        return [
            self::TYPE_GUIDE => 'guide',
            self::TYPE_ARTICLE => 'article',
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
    public function isTypeGuide()
    {
        return $this->type === self::TYPE_GUIDE;
    }

    public function setTypeToGuide()
    {
        $this->type = self::TYPE_GUIDE;
    }

    /**
     * @return bool
     */
    public function isTypeArticle()
    {
        return $this->type === self::TYPE_ARTICLE;
    }

    public function setTypeToArticle()
    {
        $this->type = self::TYPE_ARTICLE;
    }
}

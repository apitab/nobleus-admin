<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "vendor_groups".
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property float $price_of_water
 * @property int $status
 * @property string $date_created
 * @property string|null $date_modified
 *
 * @property Vendors[] $vendors
 */
class VendorGroups extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'vendor_groups';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'price_of_water', 'date_created'], 'required'],
            [['price_of_water'], 'number'],
            [['status'], 'integer'],
            [['date_created', 'date_modified'], 'safe'],
            [['name'], 'string', 'max' => 100],
            [['description'], 'string', 'max' => 250],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Name'),
            'description' => Yii::t('app', 'Description'),
            'price_of_water' => Yii::t('app', 'Price Of Water'),
            'status' => Yii::t('app', 'Status'),
            'date_created' => Yii::t('app', 'Date Created'),
            'date_modified' => Yii::t('app', 'Date Modified'),
        ];
    }

    /**
     * Gets query for [[Vendors]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVendors()
    {
        return $this->hasMany(Vendors::class, ['vendor_group' => 'id']);
    }
}

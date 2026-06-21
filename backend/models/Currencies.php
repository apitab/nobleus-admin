<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "currencies".
 *
 * @property int $id
 * @property string $name
 * @property string $code
 * @property int $status
 * @property string $date_created
 * @property string|null $date_modified
 * @property string $symbol
 *
 * @property AppSettings[] $appSettings
 * @property Customers[] $customers
 * @property Vendors[] $vendors
 */
class Currencies extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'currencies';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'code', 'date_created', 'symbol'], 'required'],
            [['status'], 'integer'],
            [['date_created', 'date_modified'], 'safe'],
            [['name'], 'string', 'max' => 100],
            [['code', 'symbol'], 'string', 'max' => 5],
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
            'code' => Yii::t('app', 'Code'),
            'status' => Yii::t('app', 'Status'),
            'date_created' => Yii::t('app', 'Date Created'),
            'date_modified' => Yii::t('app', 'Date Modified'),
            'symbol' => Yii::t('app', 'Symbol'),
        ];
    }

    /**
     * Gets query for [[AppSettings]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAppSettings()
    {
        return $this->hasMany(AppSettings::class, ['default_currency_id' => 'id']);
    }

    /**
     * Gets query for [[Customers]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCustomers()
    {
        return $this->hasMany(Customers::class, ['currency_id' => 'id']);
    }

    /**
     * Gets query for [[Vendors]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVendors()
    {
        return $this->hasMany(Vendors::class, ['currency_id' => 'id']);
    }
}

<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "payment_methods".
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property string $code
 * @property string $image
 * @property string|null $requires_phone
 * @property string|null $method_metadata
 * @property int|null $enabled
 * @property string $date_created
 * @property string|null $date_modified
 *
 * @property Payments[] $payments
 */
class PaymentMethods extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'payment_methods';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'description', 'code', 'image', 'date_created'], 'required'],
            [['description', 'requires_phone', 'method_metadata'], 'string'],
            [['enabled'], 'integer'],
            [['date_created', 'date_modified'], 'safe'],
            [['name', 'image'], 'string', 'max' => 100],
            [['code'], 'string', 'max' => 20],
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
            'code' => Yii::t('app', 'Code'),
            'image' => Yii::t('app', 'Image'),
            'requires_phone' => Yii::t('app', 'Requires Phone'),
            'method_metadata' => Yii::t('app', 'Method Metadata'),
            'enabled' => Yii::t('app', 'Enabled'),
            'date_created' => Yii::t('app', 'Date Created'),
            'date_modified' => Yii::t('app', 'Date Modified'),
        ];
    }

    /**
     * Gets query for [[Payments]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPayments()
    {
        return $this->hasMany(Payments::class, ['payment_method_id' => 'id']);
    }
}

<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "payment_statuses".
 *
 * @property int $id
 * @property string $name
 * @property string|null $status
 * @property string $date_created
 * @property string|null $date_modified
 *
 * @property Payments[] $payments
 */
class PaymentStatuses extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'payment_statuses';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'date_created'], 'required'],
            [['date_created', 'date_modified'], 'safe'],
            [['name'], 'string', 'max' => 20],
            [['status'], 'string', 'max' => 10],
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
            'status' => Yii::t('app', 'Status'),
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
        return $this->hasMany(Payments::class, ['payment_status_id' => 'id']);
    }
}

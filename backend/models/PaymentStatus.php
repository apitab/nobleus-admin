<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "payment_status".
 *
 * @property int $id
 * @property int $payment_id
 * @property string $status
 * @property string $timestamp
 * @property string|null $metadata
 *
 * @property Payments $payment
 */
class PaymentStatus extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'payment_status';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['payment_id', 'status'], 'required'],
            [['payment_id'], 'integer'],
            [['timestamp'], 'safe'],
            [['metadata'], 'string'],
            [['status'], 'string', 'max' => 50],
            [['payment_id'], 'exist', 'skipOnError' => true, 'targetClass' => Payments::class, 'targetAttribute' => ['payment_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'payment_id' => Yii::t('app', 'Payment ID'),
            'status' => Yii::t('app', 'Status'),
            'timestamp' => Yii::t('app', 'Timestamp'),
            'metadata' => Yii::t('app', 'Metadata'),
        ];
    }

    /**
     * Gets query for [[Payment]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPayment()
    {
        return $this->hasOne(Payments::class, ['id' => 'payment_id']);
    }
}

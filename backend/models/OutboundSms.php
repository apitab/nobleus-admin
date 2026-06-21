<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "outbound_sms".
 *
 * @property int $id
 * @property string $msisdn
 * @property string $message
 * @property string|null $status
 * @property string|null $date_created
 * @property string|null $date_modified
 */
class OutboundSms extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'outbound_sms';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['msisdn', 'message'], 'required'],
            [['message', 'status'], 'string'],
            [['date_created', 'date_modified'], 'safe'],
            [['msisdn'], 'string', 'max' => 15],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'msisdn' => Yii::t('app', 'Msisdn'),
            'message' => Yii::t('app', 'Message'),
            'status' => Yii::t('app', 'Status'),
            'date_created' => Yii::t('app', 'Date Created'),
            'date_modified' => Yii::t('app', 'Date Modified'),
        ];
    }
}

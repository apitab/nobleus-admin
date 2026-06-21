<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "outbound_emails".
 *
 * @property int $outbound_email_id
 * @property string $template
 * @property string $payload
 * @property int $status
 * @property string $date_created
 * @property string $date_modified
 */
class OutboundEmails extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'outbound_emails';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['template', 'payload', 'date_created'], 'required'],
            [['payload'], 'string'],
            [['status'], 'integer'],
            [['date_created', 'date_modified'], 'safe'],
            [['template'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'outbound_email_id' => 'Outbound Email ID',
            'template' => 'Template',
            'payload' => 'Payload',
            'status' => 'Status',
            'date_created' => 'Date Created',
            'date_modified' => 'Date Modified',
        ];
    }
}

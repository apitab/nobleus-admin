<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "vendor_notifications".
 *
 * @property int $id
 * @property int $vendor_id
 * @property string $title
 * @property string $message
 * @property string $type
 * @property string|null $data
 * @property int|null $status
 * @property string $date_created
 * @property string|null $date_modified
 *
 * @property Vendors $vendor
 */
class VendorNotifications extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'vendor_notifications';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['vendor_id', 'title', 'message', 'type', 'date_created'], 'required'],
            [['vendor_id', 'status'], 'integer'],
            [['data'], 'string'],
            [['date_created', 'date_modified'], 'safe'],
            [['title'], 'string', 'max' => 100],
            [['message'], 'string', 'max' => 250],
            [['type'], 'string', 'max' => 10],
            [['vendor_id'], 'exist', 'skipOnError' => true, 'targetClass' => Vendors::class, 'targetAttribute' => ['vendor_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'vendor_id' => Yii::t('app', 'Vendor ID'),
            'title' => Yii::t('app', 'Title'),
            'message' => Yii::t('app', 'Message'),
            'type' => Yii::t('app', 'Type'),
            'data' => Yii::t('app', 'Data'),
            'status' => Yii::t('app', 'Status'),
            'date_created' => Yii::t('app', 'Date Created'),
            'date_modified' => Yii::t('app', 'Date Modified'),
        ];
    }

    /**
     * Gets query for [[Vendor]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVendor()
    {
        return $this->hasOne(Vendors::class, ['id' => 'vendor_id']);
    }
}

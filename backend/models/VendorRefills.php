<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "vendor_refills".
 *
 * @property int $id
 * @property int $vendor_id
 * @property string|null $kiosk
 * @property string $volume
 * @property int $status
 * @property string $created_at
 * @property string|null $update_at
 *
 * @property Vendors $vendor
 */
class VendorRefills extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'vendor_refills';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['kiosk'], 'default', 'value' => null],
            [['vendor_id', 'volume', 'status', 'created_at'], 'required'],
            [['vendor_id', 'status'], 'integer'],
            [['created_at', 'update_at'], 'safe'],
            [['kiosk'], 'string', 'max' => 200],
            [['volume'], 'string', 'max' => 20],
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
            'kiosk' => Yii::t('app', 'Kiosk'),
            'volume' => Yii::t('app', 'Volume'),
            'status' => Yii::t('app', 'Status'),
            'created_at' => Yii::t('app', 'Created At'),
            'update_at' => Yii::t('app', 'Update At'),
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

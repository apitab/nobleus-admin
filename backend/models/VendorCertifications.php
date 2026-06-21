<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "vendor_certifications".
 *
 * @property int $id
 * @property int $vendor_id
 * @property string $certification_details
 * @property string|null $certification_meta
 * @property string $expiry_date
 * @property int $status
 * @property string $date_created
 * @property string|null $date_modified
 *
 * @property Vendors $vendor
 */
class VendorCertifications extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'vendor_certifications';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['vendor_id', 'certification_details', 'expiry_date', 'status', 'date_created'], 'required'],
            [['vendor_id', 'status'], 'integer'],
            [['certification_details', 'certification_meta'], 'string'],
            [['expiry_date', 'date_created', 'date_modified'], 'safe'],
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
            'certification_details' => Yii::t('app', 'Certification Details'),
            'certification_meta' => Yii::t('app', 'Certification Meta'),
            'expiry_date' => Yii::t('app', 'Expiry Date'),
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

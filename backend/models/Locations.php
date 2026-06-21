<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "locations".
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property int|null $status
 * @property string $date_created
 * @property string|null $date_modified
 *
 * @property Vendors[] $vendors
 */
class Locations extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'locations';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'date_created'], 'required'],
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
            'id' => 'ID',
            'name' => Yii::t('app','Name'),
            'description' => Yii::t('app','Description'),
            'status' => Yii::t('app','Status'),
            'date_created' => Yii::t('app','Date Created'),
            'date_modified' => Yii::t('app','Date Modified'),
        ];
    }

    /**
     * Gets query for [[Vendors]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVendors()
    {
        return $this->hasMany(Vendors::class, ['residential_location' => 'id']);
    }
}

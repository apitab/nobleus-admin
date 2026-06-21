<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "vendor_group_points".
 *
 * @property int $id
 * @property int $group_id
 * @property float $min_distance
 * @property float|null $max_distance
 * @property float $points_per_km
 * @property string|null $date_created
 *
 * @property VendorGroups $group
 */
class VendorGroupPoints extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'vendor_group_points';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['max_distance'], 'default', 'value' => null],
            [['group_id', 'min_distance', 'points_per_km'], 'required'],
            [['group_id'], 'integer'],
            [['min_distance', 'max_distance', 'points_per_km'], 'number'],
            [['date_created'], 'safe'],
            [['group_id'], 'exist', 'skipOnError' => true, 'targetClass' => VendorGroups::class, 'targetAttribute' => ['group_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'group_id' => Yii::t('app', 'Group ID'),
            'min_distance' => Yii::t('app', 'Min Distance'),
            'max_distance' => Yii::t('app', 'Max Distance'),
            'points_per_km' => Yii::t('app', 'Points Per Km'),
            'date_created' => Yii::t('app', 'Date Created'),
        ];
    }

    /**
     * Gets query for [[Group]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGroup()
    {
        return $this->hasOne(VendorGroups::class, ['id' => 'group_id']);
    }

}

<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "groups".
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property int $status
 * @property string $date_created
 * @property string|null $date_modified
 *
 * @property UserGroups[] $userGroups
 */
class Groups extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'groups';
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
     * Gets query for [[UserGroups]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserGroups()
    {
        return $this->hasMany(UserGroups::class, ['group_id' => 'id']);
    }
}

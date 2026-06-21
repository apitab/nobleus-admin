<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "user_groups".
 *
 * @property int $id
 * @property int $user_id
 * @property int $group_id
 * @property int $status
 * @property string $date_created
 * @property string|null $date_modified
 *
 * @property Groups $group
 * @property Users $user
 */
class UserGroups extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_groups';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'group_id', 'status', 'date_created'], 'required'],
            [['user_id', 'group_id', 'status'], 'integer'],
            [['date_created', 'date_modified'], 'safe'],
            [['group_id'], 'exist', 'skipOnError' => true, 'targetClass' => Groups::class, 'targetAttribute' => ['group_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => Users::class, 'targetAttribute' => ['user_id' => 'user_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'group_id' => 'Group ID',
            'status' => 'Status',
            'date_created' => 'Date Created',
            'date_modified' => 'Date Modified',
        ];
    }

    /**
     * Gets query for [[Group]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGroup()
    {
        return $this->hasOne(Groups::class, ['id' => 'group_id']);
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(Users::class, ['user_id' => 'user_id']);
    }
}

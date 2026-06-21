<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "permissions".
 *
 * @property int $id
 * @property int $module_id
 * @property int $action_id
 * @property int $group_id
 * @property int $status
 * @property string $date_created
 * @property string|null $date_modified
 *
 * @property ModuleActions $action
 * @property Groups $group
 * @property Modules $module
 */
class Permissions extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'permissions';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['module_id', 'action_id', 'group_id', 'status', 'date_created'], 'required'],
            [['module_id', 'action_id', 'group_id', 'status'], 'integer'],
            [['date_created', 'date_modified'], 'safe'],
            [['module_id', 'action_id', 'group_id'], 'unique', 'targetAttribute' => ['module_id', 'action_id', 'group_id']],
            [['group_id'], 'exist', 'skipOnError' => true, 'targetClass' => Groups::class, 'targetAttribute' => ['group_id' => 'id']],
            [['action_id'], 'exist', 'skipOnError' => true, 'targetClass' => ModuleActions::class, 'targetAttribute' => ['action_id' => 'id']],
            [['module_id'], 'exist', 'skipOnError' => true, 'targetClass' => Modules::class, 'targetAttribute' => ['module_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'module_id' => Yii::t('app','Module'),
            'action_id' => Yii::t('app','Action'),
            'group_id' => Yii::t('app','Group'),
            'status' => Yii::t('app','Status'),
            'date_created' => Yii::t('app','Date Created'),
            'date_modified' => Yii::t('app','Date Modified'),
        ];
    }

    /**
     * Gets query for [[Action]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAction()
    {
        return $this->hasOne(ModuleActions::class, ['id' => 'action_id']);
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
     * Gets query for [[Module]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getModule()
    {
        return $this->hasOne(Modules::class, ['id' => 'module_id']);
    }
}

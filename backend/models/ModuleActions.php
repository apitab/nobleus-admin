<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "module_actions".
 *
 * @property int $id
 * @property int $module_id
 * @property string $name
 * @property string $description
 * @property int $status
 * @property string $date_created
 * @property string|null $date_modified
 *
 * @property Modules $module
 * @property Permissions[] $permissions
 */
class ModuleActions extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'module_actions';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['module_id', 'name', 'description', 'status', 'date_created'], 'required'],
            [['module_id', 'status'], 'integer'],
            [['date_created', 'date_modified'], 'safe'],
            [['name'], 'string', 'max' => 100],
            [['description'], 'string', 'max' => 250],
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
            'module_id' => 'Module ID',
            'name' => 'Name',
            'description' => 'Description',
            'status' => 'Status',
            'date_created' => 'Date Created',
            'date_modified' => 'Date Modified',
        ];
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

    /**
     * Gets query for [[Permissions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPermissions()
    {
        return $this->hasMany(Permissions::class, ['action_id' => 'id']);
    }
}

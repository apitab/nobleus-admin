<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "modules".
 *
 * @property int $id
 * @property string $module_name
 * @property string|null $description
 * @property int $status
 * @property string $date_created
 * @property string|null $date_modified
 *
 * @property ModuleActions[] $moduleActions
 * @property Permissions[] $permissions
 */
class Modules extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'modules';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['module_name', 'date_created'], 'required'],
            [['status'], 'integer'],
            [['date_created', 'date_modified'], 'safe'],
            [['module_name'], 'string', 'max' => 100],
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
            'module_name' => 'Module Name',
            'description' => 'Description',
            'status' => 'Status',
            'date_created' => 'Date Created',
            'date_modified' => 'Date Modified',
        ];
    }

    /**
     * Gets query for [[ModuleActions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getModuleActions()
    {
        return $this->hasMany(ModuleActions::class, ['module_id' => 'id']);
    }

    /**
     * Gets query for [[Permissions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPermissions()
    {
        return $this->hasMany(Permissions::class, ['module_id' => 'id']);
    }
}

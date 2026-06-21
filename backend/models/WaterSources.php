<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "water_sources".
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property int $status
 * @property string $date_created
 * @property string|null $date_modified
 */
class WaterSources extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'water_sources';
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
            'name' => 'Name',
            'description' => 'Description',
            'status' => 'Status',
            'date_created' => 'Date Created',
            'date_modified' => 'Date Modified',
        ];
    }
}

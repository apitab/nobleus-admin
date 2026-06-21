<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "kiosks".
 *
 * @property int $id
 * @property string $name
 * @property string $location_desc
 * @property string $location_coordinates
 * @property string|null $is_open
 * @property int $status
 * @property string|null $operating_hours
 * @property string|null $date_created
 * @property string|null $date_modified
 * @property int|null $location_id
 *
 * @property Locations $location
 */
class Kiosks extends \yii\db\ActiveRecord
{

    /**
     * ENUM field values
     */
    const IS_OPEN_0 = '0';
    const IS_OPEN_1 = '1';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'kiosks';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['date_created', 'location_id'], 'default', 'value' => null],
            [['is_open'], 'default', 'value' => 1],
            [['operating_hours'], 'default', 'value' => '8AM to 5PM, Saturday to Thursday'],
            [['name', 'location_desc', 'location_coordinates', 'status'], 'required'],
            [['is_open'], 'string'],
            [['status', 'location_id'], 'integer'],
            [['date_created', 'date_modified'], 'safe'],
            [['name', 'location_coordinates'], 'string', 'max' => 100],
            [['location_desc', 'operating_hours'], 'string', 'max' => 200],
            ['is_open', 'in', 'range' => array_keys(self::optsIsOpen())],
            [['location_id'], 'exist', 'skipOnError' => true, 'targetClass' => Locations::class, 'targetAttribute' => ['location_id' => 'id']],
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
            'location_desc' => 'Location Desc',
            'location_coordinates' => 'Location Coordinates',
            'is_open' => 'Is Open',
            'status' => 'Status',
            'operating_hours' => 'Operating Hours',
            'date_created' => 'Date Created',
            'date_modified' => 'Date Modified',
            'location_id' => 'Location ID',
        ];
    }

    /**
     * Gets query for [[Location]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getLocation()
    {
        return $this->hasOne(Locations::class, ['id' => 'location_id']);
    }


    /**
     * column is_open ENUM value labels
     * @return string[]
     */
    public static function optsIsOpen()
    {
        return [
            self::IS_OPEN_0 => '0',
            self::IS_OPEN_1 => '1',
        ];
    }

    /**
     * @return string
     */
    public function displayIsOpen()
    {
        return self::optsIsOpen()[$this->is_open];
    }

    /**
     * @return bool
     */
    public function isIsOpen0()
    {
        return $this->is_open === self::IS_OPEN_0;
    }

    public function setIsOpenTo0()
    {
        $this->is_open = self::IS_OPEN_0;
    }

    /**
     * @return bool
     */
    public function isIsOpen1()
    {
        return $this->is_open === self::IS_OPEN_1;
    }

    public function setIsOpenTo1()
    {
        $this->is_open = self::IS_OPEN_1;
    }
}

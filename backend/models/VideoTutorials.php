<?php

namespace backend\models;

use Yii;
use yii\web\UploadedFile;

/**
 * This is the model class for table "video_tutorials".
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property string $video_name
 * @property int $status
 * @property string $date_created
 */
class VideoTutorials extends \yii\db\ActiveRecord
{

    public $videoFile;

    /**
    * ENUM field values
    */
   const TARGET_VENDORS = 'vendors';
   const TARGET_CUSTOMERS = 'customers';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'video_tutorials';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['status'], 'default', 'value' => 1],
            [['target', 'name', 'description', 'status', 'date_created'], 'required'],
            [['target', 'description', 'video_name'], 'string'],
            [['status'], 'integer'],
            [['date_created'], 'safe'],
            [['name'], 'string', 'max' => 200],
            [['videoFile'], 'file', 'skipOnEmpty' => true, 'extensions' => 'mp4, avi, mov, wmv, flv, webm', 'maxSize' => 100 * 1024 * 1024], // 100MB max
            ['target', 'in', 'range' => array_keys(self::optsTarget())],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'target' => Yii::t('app', 'Target'),
            'name' => Yii::t('app', 'Name'),
            'description' => Yii::t('app', 'Description'),
            'video_name' => Yii::t('app', 'Video Name'),
            'status' => Yii::t('app', 'Status'),
            'date_created' => Yii::t('app', 'Date Created'),
            'videoFile' => Yii::t('app', 'Video File'),
        ];
    }

    /**
     * Handle Video Upload
     */
    public function upload()
    {
        if ($this->validate() && $this->videoFile) {
            $uploadPath = Yii::getAlias('@webroot') . '/videos/';
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }
            $fileName = uniqid() . '.' . $this->videoFile->extension;
            $filePath = $uploadPath . $fileName;
            if ($this->videoFile->saveAs($filePath)) {
                $this->video_name = 'videos/' . $fileName; // Save relative path to DB
                return [
                    'status' => true,
                    'fileName' => $fileName,
                ];
            }
        }
        return [
            'status' => false,
            'fileName' => '',
        ];
    }

    /**
    * column target ENUM value labels
    * @return string[]
    */
   public static function optsTarget()
   {
       return [
           self::TARGET_VENDORS => Yii::t('app', 'Vendors'),
           self::TARGET_CUSTOMERS => Yii::t('app', 'Customers'),
       ];
   }


    public function displayTarget()
    {
        return self::optsTarget()[$this->target];
    }

    /** 
    * @return bool 
    */ 
   public function isTargetVendors() 
   { 
       return $this->target === self::TARGET_VENDORS; 
   } 
 
   public function setTargetToVendors() 
   { 
       $this->target = self::TARGET_VENDORS; 
   } 
 
   /** 
    * @return bool 
    */ 
   public function isTargetCustomers() 
   { 
       return $this->target === self::TARGET_CUSTOMERS; 
   } 
 
   public function setTargetToCustomers() 
   { 
       $this->target = self::TARGET_CUSTOMERS; 
    }

}

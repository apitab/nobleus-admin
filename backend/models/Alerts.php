<?php

namespace backend\models;

use Yii;
use yii\web\UploadedFile;

/**
 * This is the model class for table "alerts".
 *
 * @property int $id
 * @property string $level
 * @property string $type
 * @property string $title
 * @property string $description
 * @property string $expiry_date
 * @property string|null $attachment
 * @property int $status
 * @property string $date_created
 */
class Alerts extends \yii\db\ActiveRecord
{

    public $attachmentFile;

    /**
     * ENUM field values
     */
   const LEVEL_CRITICAL = 'critical'; 
   const LEVEL_INTERRUPTION = 'interruption'; 
   const LEVEL_DELAY = 'delay'; 
   const LEVEL_REMINDER = 'reminder'; 
   const LEVEL_GENERAL = 'general'; 
    const TYPE_VENDORS = 'vendors';
    const TYPE_CUSTOMERS = 'customers';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'alerts';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['attachment'], 'default', 'value' => null],
            [['status'], 'default', 'value' => 1],
            [['level','type', 'title', 'description', 'expiry_date', 'date_created'], 'required'],
            [['level','type', 'description'], 'string'],
            [['expiry_date', 'date_created'], 'safe'],
            [['status'], 'integer'],
            [['title', 'attachment'], 'string', 'max' => 200],
            [['attachmentFile'], 'file', 'skipOnEmpty' => true, 'extensions' => 'pdf', 'maxSize' => 10 * 1024 * 1024], // 10MB max, PDF only
            ['type', 'in', 'range' => array_keys(self::optsType())],
            ['level', 'in', 'range' => array_keys(self::optsLevel())],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'level' => Yii::t('app', 'Level'),
            'type' => 'Type',
            'title' => 'Title',
            'description' => 'Description',
            'expiry_date' => 'Expiry Date',
            'attachment' => 'Attachment',
            'attachmentFile' => Yii::t('app', 'PDF Attachment'),
            'status' => 'Status',
            'date_created' => 'Date Created',
        ];
    }

    /**
     * column level ENUM value labels
     * @return string[]
     */
    public static function optsLevel()
    {
        return [
            self::LEVEL_CRITICAL => Yii::t('app', 'Critical'),
            self::LEVEL_INTERRUPTION => Yii::t('app', 'Interruption'),
            self::LEVEL_DELAY => Yii::t('app', 'Delay'),
            self::LEVEL_REMINDER => Yii::t('app', 'Reminder'),
            self::LEVEL_GENERAL => Yii::t('app', 'General'),
        ];
    }


    /**
     * column type ENUM value labels
     * @return string[]
     */
    public static function optsType()
    {
        return [
            self::TYPE_VENDORS => 'vendors',
            self::TYPE_CUSTOMERS => 'customers',
        ];
    }

    /**
     * @return string
     */
    public function displayType()
    {
        return self::optsType()[$this->type];
    }

    /**
     * @return bool
     */
    public function isTypeVendors()
    {
        return $this->type === self::TYPE_VENDORS;
    }

    public function setTypeToVendors()
    {
        $this->type = self::TYPE_VENDORS;
    }

    /**
     * @return bool
     */
    public function isTypeCustomers()
    {
        return $this->type === self::TYPE_CUSTOMERS;
    }

    public function setTypeToCustomers()
    {
        $this->type = self::TYPE_CUSTOMERS;
    }

    /**
    * @return string
    */
   public function displayLevel() 
   { 
       return self::optsLevel()[$this->level]; 
   } 
 
   /** 
    * @return bool 
    */ 
   public function isLevelCritical() 
   { 
       return $this->level === self::LEVEL_CRITICAL; 
   } 
 
   public function setLevelToCritical() 
   { 
       $this->level = self::LEVEL_CRITICAL; 
   } 
 
   /** 
    * @return bool 
    */ 
   public function isLevelInterruption() 
   { 
       return $this->level === self::LEVEL_INTERRUPTION; 
   } 
 
   public function setLevelToInterruption() 
   { 
       $this->level = self::LEVEL_INTERRUPTION; 
   } 
 
   /** 
    * @return bool 
    */ 
   public function isLevelDelay() 
   { 
       return $this->level === self::LEVEL_DELAY; 
   } 
 
   public function setLevelToDelay() 
   { 
       $this->level = self::LEVEL_DELAY; 
   } 
 
   /** 
    * @return bool 
    */ 
   public function isLevelReminder() 
   { 
       return $this->level === self::LEVEL_REMINDER; 
   } 
 
   public function setLevelToReminder() 
   { 
       $this->level = self::LEVEL_REMINDER; 
   } 
 
   /** 
    * @return bool 
    */ 
   public function isLevelGeneral() 
   { 
       return $this->level === self::LEVEL_GENERAL; 
   } 
 
   public function setLevelToGeneral() 
   { 
       $this->level = self::LEVEL_GENERAL; 
   } 

    /**
     * Handle PDF Attachment Upload
     * @return array
     */
    public function upload()
    {
        if ($this->validate() && $this->attachmentFile) {
            $uploadDir = 'uploads/articles/';
            $uploadPath = Yii::getAlias('@webroot') . '/' . $uploadDir;
            
            // Check if directory exists, if not create it
            if (!is_dir($uploadPath)) {
                @mkdir($uploadPath, 0755, true);
                if (!is_dir($uploadPath)) {
                    return [
                        'status' => false,
                        'message' => Yii::t('app', 'Failed to create upload directory. Please contact support.'),
                    ];
                }
            }

            // Check if directory is writable
            if (!is_writable($uploadPath)) {
                return [
                    'status' => false,
                    'message' => Yii::t('app', 'Upload directory is not writable. Please contact support.'),
                ];
            }

            $fileName = uniqid() . '.' . $this->attachmentFile->extension;
            $filePath = $uploadPath . $fileName;

            if ($this->attachmentFile->saveAs($filePath)) {
                $this->attachment = $uploadDir . $fileName; // Save relative path to DB
                return [
                    'status' => true,
                    'fileName' => $fileName,
                ];
            }
        }
        return [
            'status' => false,
            'message' => Yii::t('app', 'Failed to save uploaded file. Please try again.'),
        ];
    }
}

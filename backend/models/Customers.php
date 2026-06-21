<?php

namespace backend\models;

use Yii;
use backend\helpers\Helpers;
use backend\helpers\StatusCodes;

/**
 * This is the model class for table "customers".
 *
 * @property int $id
 * @property string $alias
 * @property string|null $phone_number
 * @property int|null $primary_address
 * @property int $currency_id
 * @property string|null $language
 * @property string $password_hash
 * @property string|null $api_token
 * @property string|null $device_token
 * @property int|null $code
 * @property int $status
 * @property string $date_created
 * @property string|null $date_modified
 * @property float|null $wallet_balance 
 *
 * @property Complaints[] $complaints 
 * @property Complaints[] $complaints0 
 * @property Currencies $currency
 * @property CustomerAddress[] $customerAddresses
 * @property CustomerFavorites[] $customerFavorites
 * @property CustomerRequests[] $customerRequests
 * @property CustomerAddress $primaryAddress
 * @property TopupRequests[] $topupRequests
 * @property Notification[] $notifications
 */
class Customers extends \yii\db\ActiveRecord
{
    /** 
     * ENUM field values 
     */
    const LANGUAGE_EN = 'en';
    const LANGUAGE_SOM = 'som';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'customers';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['alias', 'currency_id', 'password_hash', 'status', 'date_created'], 'required'],
            [['primary_address', 'currency_id', 'code', 'status'], 'integer'],
            [['language', 'password_hash', 'api_token', 'device_token'], 'string'],
            [['date_created', 'date_modified'], 'safe'],
            [['alias'], 'string', 'max' => 150],
            [['phone_number'], 'string', 'max' => 100],
            [['phone_number'], 'unique'],
            [['primary_address'], 'exist', 'skipOnError' => true, 'targetClass' => CustomerAddress::class, 'targetAttribute' => ['primary_address' => 'id']],
            [['currency_id'], 'exist', 'skipOnError' => true, 'targetClass' => Currencies::class, 'targetAttribute' => ['currency_id' => 'id']],
            [['wallet_balance'], 'number'],
            [['language'], 'in', 'range' => [self::LANGUAGE_EN, self::LANGUAGE_SOM]],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'alias' => Yii::t('app', 'Alias'),
            'phone_number' => Yii::t('app', 'Phone Number'),
            'primary_address' => Yii::t('app', 'Primary Address'),
            'currency_id' => Yii::t('app', 'Currency ID'),
            'language' => Yii::t('app', 'Language'),
            'password_hash' => Yii::t('app', 'Password Hash'),
            'api_token' => Yii::t('app', 'Api Token'),
            'device_token' => Yii::t('app', 'Device Token'),
            'code' => Yii::t('app', 'Code'),
            'status' => Yii::t('app', 'Status'),
            'date_created' => Yii::t('app', 'Date Created'),
            'date_modified' => Yii::t('app', 'Date Modified'),
            'wallet_balance' => Yii::t('app', 'Wallet Balance'),
        ];
    }

    /**
     * validates the input phone number
     */
    public function validatePhoneNumber()
    {
        if (!Helpers::formatMsisdn($this->phone_number)) {
            $this->addError('mobile_number', 'Enter valid phone number in the format 634070906 or +252634070906');
        }
        $this->phone_number = Helpers::formatMsisdn($this->phone_number);
    }

    /**
     * Gets query for [[Currency]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCurrency()
    {
        return $this->hasOne(Currencies::class, ['id' => 'currency_id']);
    }

    /**
     * Gets query for [[CustomerAddresses]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCustomerAddresses()
    {
        return $this->hasMany(CustomerAddress::class, ['customer_id' => 'id']);
    }

    /**
     * Gets the total number of addresses associated with this customer
     * @return int
     */
    public function getAddressCount()
    {
        return $this->getCustomerAddresses()->count();
    }

    /**
     * Gets query for [[CustomerFavorites]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCustomerFavorites()
    {
        return $this->hasMany(CustomerFavorites::class, ['customer_id' => 'id']);
    }

    /**
     * Gets query for [[CustomerRequests]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCustomerRequests()
    {
        return $this->hasMany(CustomerRequests::class, ['customer_id' => 'id']);
    }

    /**
     * Gets query for [[PrimaryAddress]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPrimaryAddress()
    {
        return $this->hasOne(CustomerAddress::class, ['id' => 'primary_address']);
    }

    public function getOrdersCount()
    {
        return $this->hasMany(CustomerRequests::class, ['customer_id' => 'id'])->count();
    }

    /*
     * Gets query for [[Complaints]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getComplaints()
    {
        return $this->hasMany(Complaints::class, ['customer_id' => 'id']);
    }
    /**
     * Gets query for [[Complaints0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getComplaints0()
    {
        return $this->hasMany(Complaints::class, ['customer_id' => 'id']);
    }
    /**
  
    }
    /**
     * Gets query for [[Notifications]]. 
     * 
     * @return \yii\db\ActiveQuery 
     */
    public function getNotifications()
    {
        return $this->hasMany(Notification::class, ['customer_id' => 'id']);
    }

    /**
     * column language ENUM value labels
     * @return string[]
     */
    public static function optsLanguage()
    {
        return [
            self::LANGUAGE_EN => 'en',
            self::LANGUAGE_SOM => 'som',
        ];
    }
    /**
     * @return string
     */
    public function displayLanguage()
    {
        return self::optsLanguage()[$this->language];
    }
    /**
     * @return bool
     */
    public function isLanguageEn()
    {
        return $this->language === self::LANGUAGE_EN;
    }
    public function setLanguageToEn()
    {
        $this->language = self::LANGUAGE_EN;
    }
    /**
     * @return bool
     */
    public function isLanguageSom()
    {
        return $this->language === self::LANGUAGE_SOM;
    }
    public function setLanguageToSom()
    {
        $this->language = self::LANGUAGE_SOM;
    }

    public function getTotalSpent()
{
    return CustomerRequests::find()
        ->where(['customer_id' => $this->id])
        ->andWhere('status in ('.StatusCodes::PAYMENT_AWAITING_CONFIRMATION.','.StatusCodes::COMPLETED_CUSTOMER_REQUEST.')')
        ->sum('total_amount') ?? 0;
}
}

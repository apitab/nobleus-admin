<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "app_settings".
 *
 * @property int $id
 * @property string $app_name
 * @property int $default_currency_id
 * @property string $primary_color
 * @property string $primary_dark_color
 * @property string $secondary_color
 * @property string $secondary_dark_color
 * @property string $accent_color
 * @property string $accent_dark_color
 * @property string $primary_text
 * @property string $secondary_text
 * @property string $buttons_text
 * @property string $divider_color
 * @property string|null $default_mobile_language
 * @property string $default_country_code
 * @property string $app_version
 * @property int $enable_version
 * @property string $currency_decimal_digits
 * @property string $distance_unit
 * @property int|null $enable_otp
 * @property float|null $usd_sos_rate
 * @property int|null $is_online
 * @property string|null $uom_volume
 * @property float $price_per_barrel
 *
 * @property Currencies $defaultCurrency
 */
class AppSettings extends \yii\db\ActiveRecord
{

    /**
     * ENUM field values
     */
    const DEFAULT_MOBILE_LANGUAGE_EN = 'en';
    const DEFAULT_MOBILE_LANGUAGE_SOM = 'som';
    const UOM_VOLUME_LITRES = 'litres';
    const UOM_VOLUME_BARRELS = 'barrels';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'app_settings';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['default_mobile_language'], 'default', 'value' => 'en'],
            [['is_online'], 'default', 'value' => 1],
            [['uom_volume'], 'default', 'value' => 'barrels'],
            [['price_per_barrel'], 'default', 'value' => 5000],
            [['id', 'app_name', 'default_currency_id', 'primary_color', 'primary_dark_color', 'secondary_color', 'secondary_dark_color', 'accent_color', 'accent_dark_color', 'primary_text', 'secondary_text', 'buttons_text', 'divider_color', 'default_country_code', 'app_version', 'enable_version', 'currency_decimal_digits', 'distance_unit'], 'required'],
            [['id', 'default_currency_id', 'enable_version', 'enable_otp', 'is_online'], 'integer'],
            [['default_mobile_language', 'uom_volume'], 'string'],
            [['usd_sos_rate', 'price_per_barrel'], 'number'],
            [['app_name'], 'string', 'max' => 100],
            [['primary_color', 'primary_dark_color', 'secondary_color', 'secondary_dark_color', 'accent_color', 'accent_dark_color', 'primary_text', 'secondary_text', 'buttons_text', 'divider_color'], 'string', 'max' => 20],
            [['default_country_code', 'app_version', 'distance_unit'], 'string', 'max' => 10],
            [['currency_decimal_digits'], 'string', 'max' => 2],
            ['default_mobile_language', 'in', 'range' => array_keys(self::optsDefaultMobileLanguage())],
            ['uom_volume', 'in', 'range' => array_keys(self::optsUomVolume())],
            [['id'], 'unique'],
            [['default_currency_id'], 'exist', 'skipOnError' => true, 'targetClass' => Currencies::class, 'targetAttribute' => ['default_currency_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'app_name' => 'App Name',
            'default_currency_id' => 'Default Currency ID',
            'primary_color' => 'Primary Color',
            'primary_dark_color' => 'Primary Dark Color',
            'secondary_color' => 'Secondary Color',
            'secondary_dark_color' => 'Secondary Dark Color',
            'accent_color' => 'Accent Color',
            'accent_dark_color' => 'Accent Dark Color',
            'primary_text' => 'Primary Text',
            'secondary_text' => 'Secondary Text',
            'buttons_text' => 'Buttons Text',
            'divider_color' => 'Divider Color',
            'default_mobile_language' => 'Default Mobile Language',
            'default_country_code' => 'Default Country Code',
            'app_version' => 'App Version',
            'enable_version' => 'Enable Version',
            'currency_decimal_digits' => 'Currency Decimal Digits',
            'distance_unit' => 'Distance Unit',
            'enable_otp' => 'Enable Otp',
            'usd_sos_rate' => 'Usd Sos Rate',
            'is_online' => 'Is Online',
            'uom_volume' => 'Uom Volume',
            'price_per_barrel' => 'Price Per Barrel',
        ];
    }

    /**
     * Gets query for [[DefaultCurrency]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDefaultCurrency()
    {
        return $this->hasOne(Currencies::class, ['id' => 'default_currency_id']);
    }


    /**
     * column default_mobile_language ENUM value labels
     * @return string[]
     */
    public static function optsDefaultMobileLanguage()
    {
        return [
            self::DEFAULT_MOBILE_LANGUAGE_EN => 'en',
            self::DEFAULT_MOBILE_LANGUAGE_SOM => 'som',
        ];
    }

    /**
     * column uom_volume ENUM value labels
     * @return string[]
     */
    public static function optsUomVolume()
    {
        return [
            self::UOM_VOLUME_LITRES => 'litres',
            self::UOM_VOLUME_BARRELS => 'barrels',
        ];
    }

    /**
     * @return string
     */
    public function displayDefaultMobileLanguage()
    {
        return self::optsDefaultMobileLanguage()[$this->default_mobile_language];
    }

    /**
     * @return bool
     */
    public function isDefaultMobileLanguageEn()
    {
        return $this->default_mobile_language === self::DEFAULT_MOBILE_LANGUAGE_EN;
    }

    public function setDefaultMobileLanguageToEn()
    {
        $this->default_mobile_language = self::DEFAULT_MOBILE_LANGUAGE_EN;
    }

    /**
     * @return bool
     */
    public function isDefaultMobileLanguageSom()
    {
        return $this->default_mobile_language === self::DEFAULT_MOBILE_LANGUAGE_SOM;
    }

    public function setDefaultMobileLanguageToSom()
    {
        $this->default_mobile_language = self::DEFAULT_MOBILE_LANGUAGE_SOM;
    }

    /**
     * @return string
     */
    public function displayUomVolume()
    {
        return self::optsUomVolume()[$this->uom_volume];
    }

    /**
     * @return bool
     */
    public function isUomVolumeLitres()
    {
        return $this->uom_volume === self::UOM_VOLUME_LITRES;
    }

    public function setUomVolumeToLitres()
    {
        $this->uom_volume = self::UOM_VOLUME_LITRES;
    }

    /**
     * @return bool
     */
    public function isUomVolumeBarrels()
    {
        return $this->uom_volume === self::UOM_VOLUME_BARRELS;
    }

    public function setUomVolumeToBarrels()
    {
        $this->uom_volume = self::UOM_VOLUME_BARRELS;
    }
}

<?php

namespace backend\helpers;

use backend\models\AppSettings;
use yii;
use backend\models\OutboundSms;

/**
 * Helper Class
 * 
 * @author Maritim, Kip <github.com/KiprotichMaritim>
 */

class Helpers
{

    /**
     * Format MSISDN to international format
     * @param string
     * @return string
     */
    public static function formatMsisdn($phoneNumber, $countryCode = null)
    {
        if (is_null($countryCode)) {
            $countryCode = Yii::$app->params['defaultCountryCode'];
        }

        // Trim whitespace and remove leading '+' if present before stripping other chars
        $raw = trim((string) $phoneNumber);
        if ($raw !== '' && $raw[0] === '+') {
            $raw = substr($raw, 1);
        }

        // Remove all non-digits
        $digits = preg_replace('/[^0-9]/', '', $raw);

        if ($digits === null || $digits === '') {
            return false;
        }

        // Convert international prefix '00' to direct country code form
        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }

        // If number starts with country code, validate total length
        if (str_starts_with($digits, (string) $countryCode)) {
            $nationalPart = substr($digits, strlen((string) $countryCode));
            if (strlen($nationalPart) === 9) {
                return (string) $countryCode . $nationalPart;
            }
            return false;
        }

        // If local/national format (9 digits), prepend country code
        if (strlen($digits) === 9) {
            return (string) $countryCode . $digits;
        }

        return false;
    }

    /**
     * Check hour of day
     * @return string
     */
    public static function getTimeOfDay()
    {
        $hour = date("H");
        if ($hour >= 5 && $hour < 12) {
            return Yii::t('app', "Good Morning");
        } elseif ($hour >= 12 && $hour < 17) {
            return Yii::t('app', "Good Afternoon");
        } else {
            return Yii::t('app', "Good Evening");
        }
    }

    public static function getSMSTemplate($type)
    {
        switch ($type) {
            case 'FORGOT_PASSWORD_SMS':
                return Yii::t('app', 'Hello %name%, Your Dhaamiye Darawal password reset code is %code%');
            case 'ACTIVATION_SMS':
                return Yii::t("app", "Hello %name%, Your Dhaamiye Activation code is %code%");
            case 'ORDER_CANCELED_BY_VENDOR':
                return Yii::t('app', 'Hello %name%, Your Dhaamiye order for %volume% from %vendor_name% has been rejected');
            case 'VENDOR_ORDER_CREATED':
                return Yii::t('app', 'Hello %name%, REF:HWA-%ref%, You have a %single_shared% Dhaamiye order of %volume% to %address%,  %date%. %order_details%');
            case 'VENDOR_ORDER_ACCEPTED':
                return Yii::t('app', 'Hello %name%, Your Dhaamiye order for %volume% from %vendor_name% has been accepted and ready for delivery on %date%');
            case 'VENDOR_DELIVERED':
                return Yii::t('app', 'Hello %name%, Your Dhaamiye order for %volume% from %vendor_name% has been delivered on %date%');
            case 'CUSTOMER_ORDER_CREATED':
                return Yii::t('app', 'Hello %name%, Your %single_shared% Dhaamiye order for %volume% from %vendor_name% on %date% has been created successfully');
            case 'VENDOR_ORDER_REJECTED':
                return Yii::t('app', 'Hello %name%, Your Dhaamiye order for %volume% from %vendor_name% on %date% has been rejected');
            case 'VENDOR_ACCOUNT_CREATED':
                return Yii::t('app', 'Hello %name%, Your Dhaamiye vendor account has been created successfully. Your login credentials are as follows: Phone: %phone% and Password: %password%');
            case 'VENDOR_ACCOUNT_RESET':
                return Yii::t('app', 'Hello %name%, Your Dhaamiye vendor account has been reset. Your new login credentials are as follows: Phone: %phone% and Password: %password%');
            case 'USER_ACCOUNT_RESET':
                return Yii::t('app', 'Hello %name%, Your Dhaamiye account has been reset. Your new login credentials are as follows: Phone: %phone% and Password: %password%');
        }
    }

    public static function formatCurrency($amount)
    {
        return number_format($amount, 2);
    }

    public static function convertAmount($amount, $base = 'sos')
    {
        if ($base == 'sos') {
            return $amount;
        }
        $usd_sos_rate = AppSettings::findOne(1)->usd_sos_rate;
        return $amount * $usd_sos_rate;
    }

    public static function localCurrencyFormatter($amount)
    {
        $currency = 'Sl Sh'; // Custom
        return $currency . ' ' . number_format($amount);
    }

    public static function sendSMS($phone, $message)
    {
        $sms = new OutboundSms();
        $sms->msisdn = $phone;
        $sms->message = $message;
        $sms->status = 'pending';
        $sms->date_created = Date('Y-m-d H:i:s');
        $sms->date_modified = Date('Y-m-d H:i:s');
        $sms->save();
    }
}

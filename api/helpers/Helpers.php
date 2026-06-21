<?php

namespace api\helpers;

use backend\models\AppSettings;
use yii;
use backend\models\OutboundSms;
use backend\models\Notifications;

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
     * @param null
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

    public static function getSMSTemplate($type, $lang = null)
    {
        $currentLang = null;
        // Set language if provided
        if ($lang !== null) {
            $currentLang = Yii::$app->language;
            Yii::$app->language = $lang;
        }
        
        $template = '';
        switch ($type) {
            case 'FORGOT_PASSWORD_SMS':
                $template = Yii::t('app', 'Hello %name%, Your Dhaamiye Darawal password reset code is %code%');
                break;
            case 'ACTIVATION_SMS':
                $template = Yii::t("app", "Hello %name%, Your Dhaamiye Activation code is %code%");
                break;
            case 'ORDER_CANCELED_BY_VENDOR':
                $template = Yii::t('app', 'Hello %name%, Your Dhaamiye order for %volume% from %vendor_name% has been rejected');
                break;
            case 'VENDOR_ORDER_CREATED':
                $template = Yii::t('app', 'Hello %name%, REF:HWA-%ref%, You have a %single_shared% Dhaamiye order of %volume% to %address%,  %date%. %order_details%');
                break;
            case 'CUSTOMER_ORDER_ACCEPTED':
                $template = Yii::t('app', 'Hello %name%, Your Dhaamiye order for %volume% from %vendor_name% has been accepted and ready for delivery on %date%');
                break;
            case 'VENDOR_ORDER_DELIVERED':
                $template = Yii::t('app', 'Hello %name%, Your Darawal order for %volume% to %address% has been delivered on %date%');
                break;
            case 'CUSTOMER_ORDER_CREATED':
                $template = Yii::t('app', 'Hello %name%, Your %single_shared% Dhaamiye order for %volume% from %vendor_name% on %date% has been created successfully');
                break;
            case 'CUSTOMER_ORDER_REJECTED':
                $template = Yii::t('app', 'Hello %name%, Your Dhaamiye order for %volume% from %vendor_name% on %date% has been declined. Kindly select another vendor from Dhaamiye app');
                break;
            case 'CUSTOMER_ORDER_ON_THE_WAY':
                $template = Yii::t('app', 'Hello %name%, Your Dhaamiye order for %volume% from %vendor_name% is on the way to %address% on %date%');
                break;
            case 'CUSTOMER_ORDER_DELIVERED':
                $template = Yii::t('app', 'Hello %name%, Your Dhaamiye order for %volume% from %vendor_name% has been delivered on %date%');
                break;
            case 'CUSTOMER_ORDER_MARKED_AS_PAID':
                $template = Yii::t('app', 'Hello %name%, Your Dhaamiye order for %volume% from %vendor_name% has been marked as paid on %date%');
                break;
            case 'VENDOR_ACCOUNT_CREATED':
                $template = Yii::t('app', 'Hello %name%, Your Darawal vendor account has been created successfully. Your login credentials are as follows: Phone: %phone% and Password: %password%');
                break;
            case 'VENDOR_ACCOUNT_RESET':
                $template = Yii::t('app', 'Hello %name%, Your Darawal vendor account has been reset. Your new login credentials are as follows: Phone: %phone% and Password: %password%');
                break;
            case 'VENDOR_ORDER_ACCEPTED':
                $template = Yii::t('app', 'Hello %name%, You have accepted to deliver %volume% to %address% on %date%. REF-%ref%');
                break;
            case 'VENDOR_ORDER_MARKED_AS_PAID':
                $template = Yii::t('app', 'Hello %name%, Your Darawal order for %volume% to %address% has been marked as paid on %date%');
                break;
                
        }
        
        // Restore original language if it was changed
        if ($lang !== null) {
            Yii::$app->language = $currentLang;
        }
        
        return $template;
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

    public static function localCurrencyFormatter($amount) {
        $currency = 'Sl'; // Custom
        return $currency . ' ' . number_format($amount);
    } 

    public static function sendSMS($phone, $message) {
        $sms = new OutboundSms();
        $sms->msisdn = $phone;
        $sms->message = $message;
        $sms->status = 'pending';
        $sms->date_created = Date('Y-m-d H:i:s');
        $sms->date_modified = Date('Y-m-d H:i:s');
        $sms->save();
    }

    public static function haversineDistance($lat1, $lon1, $lat2, $lon2, $unit = "K") {
        // Convert degrees to radians
        $lat1 = deg2rad($lat1);
        $lon1 = deg2rad($lon1);
        $lat2 = deg2rad($lat2);
        $lon2 = deg2rad($lon2);
    
        // Haversine formula
        $dlat = $lat2 - $lat1;
        $dlon = $lon2 - $lon1;
    
        $a = sin($dlat/2) * sin($dlat/2) +
             cos($lat1) * cos($lat2) *
             sin($dlon/2) * sin($dlon/2);
    
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    
        $earthRadiusKm = 6371; // Radius of Earth in KM
        $distance = $earthRadiusKm * $c;
    
        // Convert if needed
        if ($unit == "M") { // miles
            $distance *= 0.621371;
        } elseif ($unit == "N") { // nautical miles
            $distance *= 0.539957;
        }
    
        return $distance; // returns in KM by default
    }


    /** 
     * Log a new notification for a vendor request 
     */
    public static function sendCustomerNotifications($customer, $request, $vendor = true)
    {
        $message = self::getSMSTemplate('CUSTOMER_ORDER_CREATED', $request->language);
        $message = str_replace("%name%", $customer->alias, $message);
        $message = str_replace("%volume%", $request->volume_requested, $message);
        $message = str_replace("%vendor_name%", $vendor ? $request->vendor->other_names : Yii::t('app', 'Dhaamiye'), $message);
        $message = str_replace("%date%", self::getHumanReadableDate($request->delivery_date), $message);
        $message = str_replace("%order_details%", "dhaamiye://order/" . $request->id, $message);
        $message = str_replace("%single_shared%", $request->is_shared_request == 1 ? Yii::t('app', 'Shared') : Yii::t('app', 'Single'), $message);

        //Send SMS to the customer
        $sms = new OutboundSms();
        $sms->msisdn = $customer->phone_number;
        $sms->message = $message;
        $sms->date_created = $sms->date_modified = Date('Y-m-d H:i:s');
        $sms->status = 'pending';
        $sms->save();

        //Notification
        $notification = new Notifications();
        $notification->device_id = $customer->device_token;
        $notification->data_values = json_encode([
            'request_id' => $request->id,
            'deep_link' => "dhaamiye_user://order/" . $request->id
        ]);
        $notification->key_type = 'customer';
        $notification->key_id = $customer->id;
        $notification->title = 'New Order';
        $notification->message = $message;
        $notification->type = 'order';
        $notification->not_type = 'success';
        $notification->status = StatusCodes::CREATE_STATUS;
        $notification->date_created = $notification->date_modified = date('Y-m-d H:i:s');
        $notification->save();
    }

    public static function sendVendorNotifications($vendor, $request)
    {
        //Send SMS to the vendor
        $template = self::getSMSTemplate('VENDOR_ORDER_CREATED');
        $template = str_replace("%name%", $vendor->first_name, $template);
        $template = str_replace("%ref%", $request->id, $template);
        $template = str_replace("%volume%", $request->volume_requested, $template);
        $template = str_replace("%address%", $request->customerAddress->address, $template);
        $template = str_replace("%date%", self::getHumanReadableDate($request->delivery_date), $template);
        $template = str_replace("%order_details%", "Darawal://order/" . $request->id, $template);
        $template = str_replace("%single_shared%", $request->is_shared_request == 1 ? Yii::t('app', 'Shared') : Yii::t('app', 'Single'), $template);

        //SMS
        $sms = new OutboundSms();
        $sms->msisdn = $request->vendor->mobile_number;
        $sms->message = $template;
        $sms->date_created = $sms->date_modified = Date('Y-m-d H:i:s');
        $sms->status = 'pending';
        $sms->save();

        //Notification
        if ($vendor->device_token != '') {
            $notification = new Notifications();
            $notification->device_id = $vendor->device_token;
            $notification->data_values = json_encode([
                'request_id' => $request->id,
                'deep_link' => "dhaamiye_vendor://order/" . $request->id
            ]);
            $notification->key_type = 'vendor';
            $notification->key_id = $vendor->id;
            $notification->title = 'New Order';
            $notification->message = $template;
            $notification->type = 'order';
            $notification->not_type = 'success';
            $notification->status = StatusCodes::CREATE_STATUS;
            $notification->date_created = $notification->date_modified = date('Y-m-d H:i:s');
            $notification->save();
        }
    }

    /**
     * Format date in human readable format (today, tomorrow, or date)
     */
    private static function getHumanReadableDate($date)
    {
        $today = date('Y-m-d');
        $tomorrow = date('Y-m-d', strtotime('+1 day'));
        $deliveryDate = date('Y-m-d', strtotime($date));
        
        if ($deliveryDate === $today) {
            return 'today';
        } elseif ($deliveryDate === $tomorrow) {
            return 'tomorrow';
        } else {
            return date('j,F', strtotime($date)); // e.g., "24,July"
        }
    }
}

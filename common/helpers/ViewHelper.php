<?php

namespace common\helpers;

use yii;

class ViewHelper extends \yii\helpers\ArrayHelper {
    
    /**
     * Format the status from number into a human readable text
     * 
     * @param int $status
     * @return string
     */
    public static function formatStatusIntoString($status) {
        if(!is_int($status)) {
            return 'Unknown';
        }
        
        switch($status) {
            case 1:
                return 'Active';
            case 9:
                return 'DeActivated';
            case 3:
                return 'Dormant';
            default:
                return 'Unknown';
        }
    }
    
    /**
     * Manage flash messages
     * 
     * @param null
     * @return string
     */
    public static function displayFlash() {
        if(Yii::$app->getSession()->getFlash('success')) {
            $response = '<div class="mg-y-10">';
            $response .= '<div class="alert alert-success">';
            $response .= Yii::$app->getSession()->getFlash('success');
            $response .= '</div></div>';
            return $response;
        }
        
        if(Yii::$app->getSession()->getFlash('error')) {
            $response = '<div class="mg-y-10">';
            $response .= '<div class="alert alert-danger">';
            $response .= Yii::$app->getSession()->getFlash('error');
            $response .= '</div></div>';
            return $response;
        }
    }
    
    /**
     * 
     * @param type $array
     * @param type $from
     * @param type $to
     * @param type $group
     * @return type
     */
    public static function map($array, $from, $to, $group = null)
    {
        $result = [];
        foreach ($array as $element) {
            $key = static::getValue($element, $from);
            if(is_array($to)) {
                $value = "";
                foreach($to as $val) {
                    $value .= static::getValue($element, $val) . ' - ';
                }
                trim($value);
            } else {
                $value = static::getValue($element, $to);
            }
            
            if ($group !== null) {
                $result[static::getValue($element, $group)][$key] = $value;
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    public static function formatMsisdn($country, $phone_number) {
        //@todo Format MSISDN
        return $phone_number;
    }
}
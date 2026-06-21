<?php

namespace api\helpers;

use yii;

/**
 * Status codes manager
 */

class StatusCodes
{
  //General
  const CREATE_STATUS = 0;
  const ACTIVE_STATUS = 1;
  const DELETE_STATUS = 10;


  //Customer Requests
  const NEW_CUSTOMER_REQUEST = 10;
  const NEW_CUSTOMER_REQUEST_OPEN = 11;
  const ON_THE_WAY_CUSTOMER_REQUEST = 12;
  const DELIVERED_CUSTOMER_REQUEST = 13;
  const PAYMENT_INITIATED_CUSTOMER_REQUEST = 14;
  const PAYMENT_AWAITING_CONFIRMATION = 15;
  const COMPLETED_CUSTOMER_REQUEST = 16;
  const PAYMENT_PAY_LATER_CUSTOMER_REQUEST = 17;

  const CUSTOMER_CANCELLED_REQUEST = 20;

  //Payment method statuses
  const ENABLED_PAYMENT_METHOD = 1;
  const DISABLED_PAYMENT_METHOD = 0;

  //Payment Statuses
  const PAYMENT_NEW = 110;
  const PAYMENT_CUSTOMER_CONFIRMED = 111;
  const PAYMENT_VENDOR_CONFIRMED = 112;
  const PAYMENT_SUCCESS = 114;
  const PAYMENT_FAILED = 115;
  const PAYMENT_RETRY = 116;
  const PAYMENT_PAY_LATER = 117;



  public static function getStatusText($status)
  {
    switch ($status) {
      case self::CREATE_STATUS:
        return "Created";
      case self::ACTIVE_STATUS:
        return "Active";
      case self::DELETE_STATUS:
        return 'Inactive';
    }
  }

  public static function getRequestStatusText($status)
  {
    switch ($status) {
      case StatusCodes::NEW_CUSTOMER_REQUEST:
      case StatusCodes::NEW_CUSTOMER_REQUEST_OPEN:
        return  Yii::t('app', 'New');
      case StatusCodes::ON_THE_WAY_CUSTOMER_REQUEST:
        return Yii::t('app', 'On the Way');
      case StatusCodes::DELIVERED_CUSTOMER_REQUEST:
        return Yii::t('app', 'Delivered');
      case StatusCodes::PAYMENT_INITIATED_CUSTOMER_REQUEST:
        return Yii::t('app', 'Payment Initiated');
      case StatusCodes::PAYMENT_AWAITING_CONFIRMATION:
        return Yii::t('app', 'Payment Awaiting Confirmation');
      case StatusCodes::COMPLETED_CUSTOMER_REQUEST:
        return Yii::t('app', 'Paid');
      case 20:
        return Yii::t('app', 'Cancelled');
    }
  }

  public static function getPaymentStatusText($status) {
    switch($status) {
      case self::PAYMENT_NEW:
        return Yii::t('app','Pending processing');
      case self::PAYMENT_CUSTOMER_CONFIRMED:
        return Yii::t('app','Customer Confirmed');
      case self::PAYMENT_VENDOR_CONFIRMED:
        return Yii::t('app','Vendor Confirmed');
      case self::PAYMENT_INITIATED_CUSTOMER_REQUEST:
        return Yii::t('app','Request Initiated to customer');
      case self::PAYMENT_SUCCESS:
        return Yii::t('app','Payment Successfull');
      case self::PAYMENT_FAILED:
        return Yii::t('app','Payment Failed');
      case self::PAYMENT_RETRY:
        return Yii::t('app','Payment marked for retry');
      case self::PAYMENT_PAY_LATER:
        return Yii::t('app','Payment Pay Later');
      default:
        return Yii::t('app','Unknown');
    }
  }

  public static function getPaymentStatusConstants() {
    return [
      self::PAYMENT_NEW => Yii::t('app','Pending processing'),
      self::PAYMENT_CUSTOMER_CONFIRMED => Yii::t('app','Customer Confirmed'),
      self::PAYMENT_VENDOR_CONFIRMED => Yii::t('app','Vendor Confirmed'),
      self::PAYMENT_SUCCESS => Yii::t('app','Payment Successfull'),
      self::PAYMENT_FAILED => Yii::t('app','Payment Failed'),
      self::PAYMENT_RETRY => Yii::t('app','Payment marked for retry'),
    ];
  }
}

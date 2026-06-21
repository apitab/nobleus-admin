<?php

namespace backend\models;

use Yii;
use yii\base\Model;

/**
 * Range Filter form
 */
class PaymentSearchForm extends Model
{
    public $customer_phone_number;
    public $vendor_phone_number;
    public $from_date;
    public $to_date;
    public $payment_status;
    public $payment_method;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            
        ];
    }

    /**
     * Attribute labels
     */
    public function attributeLabels() {
        return [
            
        ];
    }

}

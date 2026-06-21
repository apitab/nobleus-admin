<?php

namespace backend\models;

use Yii;
use yii\base\Model;

/**
 * Range Filter form
 */
class PaymentsSearchForm extends Model
{
    public $dateFrom;
    public $dateTo;
    public $paymentMethod;
    public $status;
    public $customerPhoneNumber;
    public $vendorPhoneNumber;
    

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['dateFrom', 'dateTo'], 'date', 'format' => 'php:Y-m-d'],
            [['paymentMethod', 'status', 'customerPhoneNumber', 'vendorPhoneNumber'], 'safe'],
        ];
    }

    /**
     * Attribute labels
     */
    public function attributeLabels() {
        return [
            'dateFrom' => 'From Date',
            'dateTo' => 'To Date',
            'paymentMethod' => 'Payment Method',
            'status' => 'Status',
            'customerPhoneNumber' => 'Customer Phone Number',
            'vendorPhoneNumber' => 'Vendor Phone Number',
        ];
    }

    /**
     * Check if any filters are active
     * @return boolean
     */
    public function hasFilters()
    {
        return !empty($this->customerPhoneNumber) 
            || !empty($this->vendorPhoneNumber)
            || !empty($this->dateFrom)
            || !empty($this->dateTo)
            || (!is_null($this->paymentMethod) && $this->paymentMethod !== '');
    }
}

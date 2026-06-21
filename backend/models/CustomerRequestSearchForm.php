<?php

namespace backend\models;

use Yii;
use yii\base\Model;

/**
 * Range Filter form
 */
class CustomerRequestSearchForm extends Model
{
    public $customer_phone_number;
    public $vendor_phone_number;
    public $date_from;
    public $date_to;
    public $location;
    public $vendor_group;
    public $water_source;
    public $status;
    

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

    public function hasFilters()
    {
        return !empty($this->customer_phone_number) ||
               !empty($this->vendor_phone_number) ||
               !empty($this->date_from) ||
               !empty($this->date_to) ||
               $this->status !== '';
    }

}

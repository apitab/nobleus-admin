<?php

namespace backend\models;

use Yii;
use yii\base\Model;

/**
 * Range Filter form
 */
class WithdrawalsSearchForm extends Model
{
    public $phoneNumber;
    public $firstName;
    public $lastName;
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

    /**
     * Check if any filters are active
     * @return boolean
     */
    public function hasFilters()
    {
        return !empty($this->phoneNumber) 
            || (!is_null($this->status) && $this->status !== '');
    }

}

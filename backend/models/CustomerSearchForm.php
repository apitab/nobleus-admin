<?php

namespace backend\models;

use Yii;
use yii\base\Model;

/**
 * Range Filter form
 */
class CustomerSearchForm extends Model
{
    public $phone_number;
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
        return !empty($this->phone_number) 
            || (!is_null($this->status) && $this->status !== '');
    }
}

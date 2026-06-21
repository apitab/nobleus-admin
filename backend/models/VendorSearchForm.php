<?php

namespace backend\models;

use Yii;
use yii\base\Model;

/**
 * Range Filter form
 */
class VendorSearchForm extends Model
{
    public $phone_number;
    public $first_name;
    public $last_name;
    public $location;
    public $vendor_group;
    public $tank_volume;
    public $water_source;
    public $status;
    public $vendor_type;
    

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

<?php

namespace backend\models;

use Yii;
use yii\base\Model;

/**
 * Range Filter form
 */
class UserSearchForm extends Model
{
    public $phone_number;
    public $email_address;
    public $group;
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

}

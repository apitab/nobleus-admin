<?php

namespace backend\models;

use Yii;
use yii\base\Model;

/**
 * Range Filter form
 */
class KioskSearchForm extends Model
{
    public $name;
    public $is_open;
    public $status;

    public $location;
    

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

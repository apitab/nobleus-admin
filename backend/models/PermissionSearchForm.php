<?php

namespace backend\models;

use Yii;
use yii\base\Model;

/**
 * Range Filter form
 */
class PermissionSearchForm extends Model
{
    public $group;
    public $module;
    

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

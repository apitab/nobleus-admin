<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use backend\helpers\Helpers;

class MyAccountForm extends Model {

    public $names;
    public $phone_number;
    public $language;

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['names','phone_number','language'], 'required'],
            ['phone_number','validatePhoneNumber']
        ];
    }

    /**
     * validates the input phone number
     */
    public function validatePhoneNumber() {
        if(!Helpers::formatMsisdn($this->phone_number)) {
            $this->addError('mobile_number','Enter valid phone number in the format 634070906 or +252634070906');
        }
        $this->phone_number = Helpers::formatMsisdn($this->phone_number);
    }

}

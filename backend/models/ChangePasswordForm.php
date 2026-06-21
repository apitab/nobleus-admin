<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use common\models\MerchantUsers;

class ChangePasswordForm extends Model {

    public $oldPassword;
    public $newPassword;
    public $confirmNewPassword;

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['oldPassword', 'newPassword','confirmNewPassword'], 'required'],
            ['newPassword', 'compare', 'compareAttribute' => 'confirmNewPassword'],
            ['oldPassword','validateOldPassword']
        ];
    }

    //Validates the old password
    public function validateOldPassword() {
        $user = Users::findByUsername(Yii::$app->user->identity->email_address);
        if (!$user || !$user->validatePassword($this->oldPassword)) {
           $this->addError('oldPassword', Yii::t('app','Your old password is incorrect'));
        }
    }

}

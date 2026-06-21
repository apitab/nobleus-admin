<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use backend\models\Users;
use backend\models\OutboundEmails;
use backend\helpers\StatusCodes;

/**
 * Password reset request form
 */
class PasswordResetRequestForm extends Model
{
    public $email_address;


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['email_address', 'trim'],
            ['email_address', 'required'],
            ['email_address', 'email'],
            //@todo - set generic message that if a user with that email address exists
            ['email_address', 'exist',
                'targetClass' => '\backend\models\Users',
                'filter' => ['status' => StatusCodes::ACTIVE_STATUS],
                'message' => 'Sorry, we could not find an account associated with the email address'
            ],
        ];
    }

    public function attributeLabels()
    {
        return [
            'email_address' => 'Email Address / Cinwaanka iimaylka'
        ];
    }

    /**
     * Sends an email with a link, for resetting the password.
     *
     * @return bool whether the email was send
     */
    public function sendEmail()
    {
        
        /* @var $user User */
        $user = Users::findOne([
            'status' => StatusCodes::ACTIVE_STATUS,
            'email_address' => $this->email_address,
        ]);

        if (!$user) {
            return false;
        }
        
        $passwordResetToken = "";
        if (!Users::isPasswordResetTokenValid($user->password_reset_token)) {
            $passwordResetToken = $user->generatePasswordResetToken();
            if (!$user->save()) {
                return false;
            }
        }

        $email = new OutboundEmails();
        $email->status = 0;
        $email->date_created = $email->date_modified = Date('Y-m-d H:i:s');
        $email->template = 'reset_password';
        $email->payload = json_encode([
            'to' => $this->email_address,
            'token' => $user->password_reset_token
        ]);
        return $email->save();
    }
}

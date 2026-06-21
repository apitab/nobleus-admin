<?php

namespace backend\models;

use backend\helpers\StatusCodes;
use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use backend\helpers\Helpers;

/**
 * This is the model class for table "users".
 *
 * @property int $user_id
 * @property string $email_address
 * @property string $names
 * @property string $phone_number
 * @property int $group_id
 * @property string $language
 * @property int $status
 * @property string $date_created
 * @property string|null $date_modified
 * @property string $password_hash
 * @property string|null $password_reset_token
 * @property string|null $verification_token
 * @property string|null $auth_key
 */
class Users extends ActiveRecord implements IdentityInterface
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'users';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['email_address', 'phone_number', 'group_id','status', 'date_created', 'names', 'password_hash','language'], 'required'],
            [['status','group_id'], 'integer'],
            [['date_created', 'date_modified'], 'safe'],
            [['password_hash', 'password_reset_token', 'verification_token', 'auth_key','language'], 'string'],
            [['email_address'], 'string', 'max' => 100],
            [['phone_number'], 'string', 'max' => 50],
            [['phone_number'], 'unique'],
            [['email_address'], 'unique'],
            ['phone_number', 'validatePhoneNumber'],
            [['group_id'], 'exist', 'skipOnError' => true, 'targetClass' => Groups::class, 'targetAttribute' => ['group_id' => 'id']],
        ];
    }

    /**
     * validates the input phone number
     */
    public function validatePhoneNumber()
    {
        if (!Helpers::formatMsisdn($this->phone_number)) {
            $this->addError('phone_number', 'Enter valid phone number in the format 634070906 or +252634070906');
        }
        $this->phone_number = Helpers::formatMsisdn($this->phone_number);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'user_id' => 'User ID',
            'email_address' => 'Email Address',
            'names' => 'Full Names',
            'phone_number' => 'Phone Number',
            'status' => 'Status',
            'group_id' => 'User Group',
            'date_created' => 'Date Created',
            'date_modified' => 'Date Modified',
            'password_hash' => 'Password Hash',
            'password_reset_token' => 'Password Reset Token',
            'verification_token' => 'Verification Token',
            'auth_key' => 'Auth Key',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        return static::findOne(['user_id' => $id, 'status' => StatusCodes::ACTIVE_STATUS]);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['email_address' => $username, 'status' => StatusCodes::ACTIVE_STATUS]);
    }

    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @return static|null
     */
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
            'password_reset_token' => $token,
            'status' => StatusCodes::ACTIVE_STATUS,
        ]);
    }

    /**
     * Finds user by verification email token
     *
     * @param string $token verify email token
     * @return static|null
     */
    public static function findByVerificationToken($token)
    {

        return static::findOne([
            'verification_token' => $token,
            'status' => StatusCodes::ACTIVE_STATUS
        ]);
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return bool
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }

        $timestamp = (int) substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        return $timestamp + $expire >= time();
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Generates new token for email verification
     */
    public function generateEmailVerificationToken()
    {
        $this->verification_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }

    public function getGroup()
    {
        return $this->hasOne(Groups::class, ['id' => 'group_id']);
    }
}

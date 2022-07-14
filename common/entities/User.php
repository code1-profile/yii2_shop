<?php
namespace common\entities;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;


class User extends ActiveRecord implements IdentityInterface
{
    use InstantiateTrait;

    const STATUS_WAIT = 0;
    const STATUS_ACTIVE = 10;

    public static function requestSignup(string $username, string $email, string $password) : self //вернёт себя
    {
        $user = new static(); //для наследования
        $user->username = $username;
        $user->email = $email;
        $user->setPassword($password);
        $user->created_at = time();
        $user->status = self::STATUS_ACTIVE;
        $user->generateEmailConfirmToken();
        $user->generateAuthKey();
        return $user;
    }

    public function confirmSignup() : void
    {
        if( !$this->isWait() ){
            throw new \DomainException('User is already active.');
        }
        $this->status = self::STATUS_ACTIVE;
        $this->removeEmailConfirmToken();
    }

    public function requestPasswordReset(): void{
        if( !empty( $this->password_reset_token ) && self::isPasswordResetTokenValid( $this->password_reset_token ) ){
            throw new \DomainException('Password resetting is already requested.');
        }
        $this->password_reset_token = Yii::$app->security->generateRandomString().'_'.time();
    }

    public function resetPassword($password): void{
        if( empty( $this->password_reset_token ) ){
            throw new \DomainException('Password resetting is not requested');
        }
        $this->setPassword($password);
        $this->password_reset_token = null; //чтобы в сл. раз этот ресет для этого токена не вызвался.
    }

    public function isWait(): bool //add
    {
        return $this->status === self::STATUS_WAIT;
    }

    public function isActive(): bool //add
    {
        return $this->status === self::STATUS_ACTIVE;
    }


    public static function tableName()
    {
        return '{{%user}}';
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }


    public function rules()
    {
        return [
            ['status', 'default', 'value' => self::STATUS_WAIT],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_WAIT]],
        ];
    }



//username
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }


//id
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }


//password_hash
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }


    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

//auth_key

    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    public function getAuthKey()
    {
        return $this->auth_key;
    }

    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

//verification_token
    public function generateEmailConfirmToken() //add
    {
        $this->verification_token = Yii::$app->security->generateRandomString();  //was:  . '_' . time();
    }

    public function removeEmailConfirmToken() //add
    {
        $this->verification_token = null;
    }

    public static function findByVerificationToken($token) {
        return static::findOne([
            'verification_token' => $token,
            'status' => self::STATUS_WAIT //STATUS_INACTIVE
        ]);
    }


//AccessToken

    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }


//password_reset_token
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }

    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }

        $timestamp = (int) substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        return $timestamp + $expire >= time();
    }

    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
            'password_reset_token' => $token,
            'status' => self::STATUS_ACTIVE,
        ]);
    }
}

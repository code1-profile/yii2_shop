<?php
namespace frontend\services\auth;

use common\entities\User;
use frontend\forms\PasswordResetRequestForm;
use frontend\forms\ResetPasswordForm;
use yii\mail\MailerInterface;

class PasswordResetService{

    private $mailer;

    public function __construct(MailerInterface $mailer){
        phpinfo();
        $this->mailer = $mailer;
    }

    public function request(PasswordResetRequestForm $form): void
    {
        $user = User::findOne([
            'status' => User::STATUS_ACTIVE,
            'email' => $form->email,
        ]);

        if (!$user) {
            throw new \DomainException('User is not found.');
        }

        $user->requestPasswordReset();

        if(!$user->save()){
            throw new \DomainException('Saving error.');
        }

        $send = $this->mailer
            ->compose(
                ['html' => 'passwordResetToken-html', 'text' => 'passwordResetToken-text'],
                ['user' => $user]
            )
            ->setFrom($this->supportEmail)
            ->setTo($user->email)
            ->setSubject('Password reset for ' . Yii::$app->name)
            ->send();

        if(!$send){
            throw new \RuntimeException('Sending error.');
        }
    }

    public function validateToken($token): void{
        if( empty($token) || !is_string($token) ){
            throw new \DomainException('Password reset token can not be blank');
        }
        if( !User::findByPasswordResetToken( $token ) ){
            throw new \DomainException('Wrong password reset token.');
        }
    }

    public function reset(string $token, ResetPasswordForm $form): void{
        $user = User::findByPasswordResetToken($token);
        if(!$user){
            throw new \DomainException('User is not found.');
        }

        $user->resetPassword($form->password);

        if(!$user->save()){
            throw new \RuntimeException('Saving error.');
        }
    }
}
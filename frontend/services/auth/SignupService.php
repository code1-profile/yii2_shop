<?php
namespace frontend\services\auth;

use common\entities\User;
use frontend\forms\SignupForm;


class SignupService
{
    public function signup(SignupForm $form) : User
    {
        if( User::find()->andWhere(['username'=>$form->username]) ){
            throw new \DomainException('Username is already exists.');
        }

        if( User::find()->andWhere(['email'=>$form->email]) ){
            throw new \DomainException('Email is already exists.');
        }

        $user = User::signup(
            $this->username,
            $this->email,
            $this->password
        );

        if(!$user->save()){
            throw new \HttpRuntimeException('Saving error.');
        }

        return $user;
    }
}
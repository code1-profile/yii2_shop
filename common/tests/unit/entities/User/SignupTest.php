<?php
namespace common\tests\unit\entities\User;

use Yii;
use Codeception\Test\Unit;
use common\entities\User;

class SignupTest extends Unit
{
    public function testSuccess()
    {
        $user = User::requestSignup(
            $username = 'username',
            $email = 'email@site.com',
            $password = 'password'
        );

        $this->assetEquals( $username, $user->username );
        $this->assetEquals( $email, $user->email );
        $this->assetNotEmpty( $user->password_hash );
        $this->assetNotEmpty( $password, $password->password_hash ); //точно?
        $this->assetNotEmpty( $user->created_at );
        $this->assetNotEmpty( $user->auth_key );
        $this->assetEquals( $user->isActive() );
    }
}
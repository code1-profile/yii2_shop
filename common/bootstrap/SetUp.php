<?php
namespace common\bootstrap;

use Yii;
use yii\base\BootstrapInterface;
use frontend\services\auth\PasswordResetService;
use frontend\services\contact\ContactService;
use yii\mail\MailerInterface;
use yii\di\Instance;

class SetUp implements BootstrapInterface{
    public function bootstrap($app)
    {
        $container = \Yii::$container;

        $container->setSingleton(PasswordResetService::class);

        $container->setSingleton(MailerInterface::class,function () use ($app){
            return $app->mailer;
        });

        $container->setSingleton(ContactService::class,[],[
            $app->params,
        ]);
    }
}
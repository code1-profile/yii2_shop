<?php
namespace frontend\services\contact;

use yii\mail\MailerInterface;
use frontend\forms\ContactForm;


class ContactService
{
    private $mailer;
    private $adminEmail;

    public function __construct($adminEmail, MailerInterface $mailer){
        $this->adminEmail = $adminEmail;
        $this->mailer = $mailer;
    }

    public function send(ContactForm $form):void{
        $sent = $this->mailer->compose()
            ->setTo($this->adminEmail)
            ->setFrom($this->supportEmail)
            ->setSubject($form->subject)
            ->setTextBody($form->body)
            ->send();
    }
}
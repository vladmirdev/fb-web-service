<?php
/**
 * Created by PhpStorm.
 * User: zhek
 * Date: 10.10.17
 * Time: 11:16
 */

return [
    'class' => 'yii\swiftmailer\Mailer',
    'useFileTransport' => false,
    'transport' => [
        'class' => 'Swift_SmtpTransport',
        'host' => 'email-smtp.us-west-2.amazonaws.com',
        'username' => 'AKIAJNKBCRCOEST7K7TQ',
        'password' => 'AiW/w6cM9X8bbD5g+8eOmMsNOplraNoznTcw0zIV73Q4',
        'port' => '587',
        'encryption' => 'tls',
    ],
];
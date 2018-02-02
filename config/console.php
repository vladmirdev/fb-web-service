<?php
/**
 * Created by PhpStorm.
 * User: zhek
 * Date: 08.09.17
 * Time: 14:06
 */

$params = array_merge(
    require __DIR__ . '/params.php'
);

$config = [
    'id' => 'fb-api-cli',
    'name' => 'FormulaBuilder',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log', 'queue'],
    'controllerNamespace' => 'app\commands',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'controllerMap' => [
        'fixture' => [
            'class' => 'yii\console\controllers\FixtureController',
            'namespace' => 'common\fixtures',
        ],
    ],
    'components' => [
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'class' => '',
            'enableAutoLogin' => false,
        ],
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
        ],
        'mailer' => require(__DIR__ . '/mailer.php'),
        'db' => require(__DIR__ . '/db.php'),
        'queue' => require(__DIR__ . '/queue.php')
    ],
    'params' => $params,
];

return $config;

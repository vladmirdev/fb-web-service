<?php
/**
 * Created by PhpStorm.
 * User: zhek
 * Date: 08.09.17
 * Time: 7:40
 */

$params = array_merge(
    require(__DIR__ . '/params.php')
);

$config = [
    'id' => 'fb-api-server',
    'name' => 'FormulaBuilder',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log', 'queue'],
    'modules' => [
        'v1' => [
            'basePath' => '@app/modules/v1',
            'class' => 'app\modules\v1\Module',
        ]
    ],
    'components' => [
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => false,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning', 'info'],
                ],
            ],
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'enableStrictParsing' => false,
            'showScriptName' => false,
            'rules' => require(__DIR__ . '/routes.php')
        ],
        'request' => [
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ],
            'cookieValidationKey' => 'SoMeRanD0MsTr!ngsH3re',
        ],
        'response' => [
            // ...
            'formatters' => [
                \yii\web\Response::FORMAT_JSON => [
                    'class' => 'yii\web\JsonResponseFormatter',
                    'prettyPrint' => YII_DEBUG, // use "pretty" output in debug mode
                    'encodeOptions' => JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK
                ],
            ],
        ],
        'mailer' => require(__DIR__ . '/mailer.php'),
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
        ],
        'db' => require(__DIR__ . '/db.php'),
        'queue' => require(__DIR__ . '/queue.php'),

        'errorHandler' => [
            'errorAction' => 'v1/error',
        ],
    ],
    'params' => $params,
];

if (!YII_ENV_TEST) {

    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        'allowedIPs' => ['192.168.83.1', '127.0.0.1'],
        'historySize' => 1000
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        'allowedIPs' => ['192.168.83.1', '127.0.0.1'],
    ];
}

return $config;

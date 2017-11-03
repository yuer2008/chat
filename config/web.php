<?php

$params = require(__DIR__ . '/params.php');

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'defaultRoute' => 'site',
    'modules' => [
        'admin' => [
            'class' => 'app\modules\admin\module',
        ],
    ],
    'timeZone' => 'Asia/shanghai',
    'components' => [
     		'assetManager' => [
            // 'bundles' => [
                // 'yii\web\JqueryAsset' => [
                    // 'sourcePath' => null,   // do not publish the bundle
                    // 'jsOptions' => ['condition' => 'lte IE10'],
                    // 'js' => [
                    //     'http://libs.baidu.com/jquery/1.11.1/jquery.js',
                    // ]
                // ],
            // ],
        ],
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'f9whVOg-VM20nLdHCpEegddBRHvCk8uU',
        ],
              
        'session'=>[
            'class' => 'yii\web\Session',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\Mber',
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => require(__DIR__ . '/db.php')
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class'=>'yii\debug\Module',
       'allowedIPs'=>['127.0.0.1','::1',]
    ];

}

return $config;

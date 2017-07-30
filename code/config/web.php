<?php

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log', 'session'],
    'modules' => [
        'api' => [
            'class' => 'app\api\ApiModule',
        ]
    ],
    'components' => [
        'request' => [
            'enableCookieValidation' => false,
            'enableCsrfValidation' => false,
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
                'text/json' => 'yii\web\JsonParser',
            ]
        ],
        'response' =>[
            'on beforeSend' => function ( $event ) {
                $response = $event-> sender ;
                if ($response -> statusCode >= 400 ){
                    /*$error_log = new \app\api\common\models\ErrorLog() ;
                    $error_log -> exception = substr(strval(Yii:: $app ->errorHandler -> exception), 0, 2000);
                    $error_log -> path_info = substr(Yii:: $app ->request -> pathInfo, 0, 500);
                    $error_log -> body_params = substr(\yii\helpers\VarDumper::dumpAsString (Yii:: $app-> request ->bodyParams ), 0, 2000);
                    $error_log -> save() ;

                    $alarm = $response->headers->get('alarm');
                    if($alarm !== 0){
                        $response->headers->set('alarm', 1);
                    }*/
                }
            },
        ] ,
        'cache' => [
              'class' => 'yii\caching\FileCache',
//            'class' => 'yii\redis\Cache',
        ],
        'user' => [
            'identityClass' => 'app\api\common\models\User',
            'enableAutoLogin' => true,
        ],
        'authManager' => [
            'class' => 'yii\rbac\PhpManager',
        ],
        'urlManager' => require(__DIR__ . '/route.php'),
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => "mysql:host=localhost;dbname=lls",
            'username' => 'root',
            'password' => 'password',
            'charset' => 'utf8mb4',
            'tablePrefix' => 'cv_',
        ],
        'eventHandler'=>function(){
            $class_path = 'app\api\common\custom\DefaultAppEvent';
            return Yii::createObject($class_path);
        }
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = 'yii\debug\Module';

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        'allowedIPs' => ['127.0.0.1', '::1', '10.21.26.*', '101.230.11.250', '10.21.109.24'],
    ];
}

return $config;

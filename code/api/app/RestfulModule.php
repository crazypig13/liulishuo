<?php
/**
 * Author: Christian Sheng
 * Date  : 2015-09-23
 */

namespace app\api\app;

use Yii;
use \yii\base\Module;
use yii\filters\AccessControl;
use yii\web\ForbiddenHttpException;
use yii\web\UnauthorizedHttpException;

class RestfulModule extends Module
{
    public $controllerNamespace = 'app\api\app\controllers';

    public function init()
    {
        parent::init();

    }

    function behaviors()
    {
        return [
            [
                'class' => AccessControl::className(),
                'denyCallback' => function () {
                    $guest = Yii::$app->user->isGuest;
                    if (!$guest) {
                        throw new ForbiddenHttpException ('无权限操作');
                    } else {
                        throw new UnauthorizedHttpException('请返回登陆');
                    }
                },
                'rules' => [
                    [
                        'allow' => true,
                        'controllers' => ['api/app/user'],
                        'actions' => ['mock', 'test', 'register', 'login'],
                        'roles' => ['?'],
                    ],
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'allow' => false
                    ]
                ],
            ]
        ];
    }
}

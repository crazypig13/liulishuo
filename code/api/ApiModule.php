<?php
/**
 * Author: Christian Sheng
 * Date  : 2015-09-23
 */

namespace app\api;

use Yii;
use \yii\base\Module;
use yii\helpers\VarDumper;
use yii\web\ForbiddenHttpException;
use yii\web\UnauthorizedHttpException;

class ApiModule extends Module
{
    public $controllerNamespace = 'app\api\controllers';

    public function init()
    {
        parent::init();

        $this->modules = [
            'app' => [
                'class' => 'app\api\app\RestfulModule',
            ],
        ];

        // custom initialization code goes here
    }
}

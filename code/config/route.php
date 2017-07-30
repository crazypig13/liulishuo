<?php
/**
 * Author: Christian Sheng
 * Date  : 2015-09-29
 */

return [
    'class' => 'yii\web\UrlManager',
    'enablePrettyUrl' => true,
    'enableStrictParsing' => true,
    'showScriptName' => false,

    'rules' => [ // per api per rule
        [
            'class' => 'app\api\common\util\ApiUrlRule',
            'pattern' => '<controller:\w>/<action:\w>',
            'route' => '<controller>/<action>'
        ],

        /********************** Non-Restful rules starts here**********************************/
        '<controller:\w+>/<action:\w+>' => '<controller>/<action>',
        '<module:\w+>/<controller:\w+>/<id:\d+>' => '<module>/<controller>/index',
        '<module:\w+>/<controller:\w+>/' => '<module>/<controller>/index',
        '<module:\w+>/<controller:\w+>/<action:\w+>' => '<module>/<controller>/<action>',
        '<module:\w+>/<controller:\w+>/<action:\w+>/<id:\d+>' => '<module>/<controller>/<action>',
    ],

];
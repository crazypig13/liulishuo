<?php
/**
 * Created by PhpStorm.
 * User: wangyou
 * Date: 2015/9/8
 * Time: 13:29
 */

namespace app\api\app\controllers;

use app\api\common\models\Mobile;
use app\api\common\models\User;
use app\api\common\models\UserBuddy;
use app\api\common\models\Util;
use app\api\common\util\SmsHelper;
use Yii;
use yii\web\BadRequestHttpException;
use yii\rest\Controller;
use yii\web\NotFoundHttpException;

class UserController extends Controller
{
    function actionLogout(){
        Yii::$app->user->logout();
        return Util::success();
    }

    function actionInfo(){
        return User::findOne(Yii::$app->user->id);
    }

    function actionLogin(){
        $params = Yii::$app->request->bodyParams;
        return User::login($params);
    }

    function actionRegister(){
        $params = Yii::$app->request->bodyParams;

        $user = User::edit($params);
        return $user;
    }

    function actionView($id){
        if(!UserBuddy::checkFriend($id)){
            throw new BadRequestHttpException("不能查看非好友信息");
        }
        return User::findOne($id);
    }
}

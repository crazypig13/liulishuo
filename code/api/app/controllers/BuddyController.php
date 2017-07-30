<?php
/**
 * Created by PhpStorm.
 * User: wangyou
 * Date: 2015/9/8
 * Time: 13:29
 */

namespace app\api\app\controllers;

use app\api\common\components\AppEvent;
use app\api\common\models\Mobile;
use app\api\common\models\User;
use app\api\common\models\UserBuddy;
use app\api\common\models\Util;
use app\api\common\util\SmsHelper;
use Yii;
use yii\web\BadRequestHttpException;
use yii\rest\Controller;
use yii\web\NotFoundHttpException;

class BuddyController extends Controller
{
    function actionIndex(){
        User::setFormat('buddy_list');
        Yii::$app->eventHandler->send(AppEvent::EVENT_USER_READED);
        return User::queryMyBuddy();
    }

    function actionCheckNew(){
        return UserBuddy::checkNew();
    }

    function actionCreate(){
        $params = Yii::$app->request->bodyParams;
        $user1_id = intval(Yii::$app->user->id);

        $user2 = User::findOneOrError(['mail'=>$params['mail']], "该账号不存在");
        $item = UserBuddy::addFriend($user1_id, $user2->id);
        return $item;
    }

    function actionDelete($id){
        $item = UserBuddy::info($id);
        if(!$item){
            throw new BadRequestHttpException("不能删除非好友");
        }
        $self_id = Yii::$app->user->id;
        if($item->user1_id != $self_id && $item->user2_id != $self_id){
            throw new BadRequestHttpException("不能删除非本人的好友");
        }
        $item->disableBuddy();
        return Util::success("已删除");
    }
}

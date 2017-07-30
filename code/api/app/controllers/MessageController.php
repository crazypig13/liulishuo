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
use app\api\common\models\UserBuddyMessage;
use app\api\common\models\Util;
use app\api\common\util\SmsHelper;
use Yii;
use yii\web\BadRequestHttpException;
use yii\rest\Controller;
use yii\web\NotFoundHttpException;

class MessageController extends Controller
{
    function actionIndex(){
        $to_id = intval(Yii::$app->request->get('to_id'));
        User::findOneOrError($to_id, "用户不存在");
        if(!UserBuddy::checkFriend($to_id)){
            throw new BadRequestHttpException("该用户还不是好友");
        }

        $buddy = UserBuddy::info($to_id);
        Yii::$app->eventHandler->send(AppEvent::EVENT_BUDDY_READED, $buddy);


        return UserBuddyMessage::queryByBuddy(Yii::$app->request->get());
    }

    function actionCreate(){
        $params = Yii::$app->request->bodyParams;
        $to_id = intval($params['to_id']);
        User::findOneOrError($to_id, "用户不存在");
        if(!UserBuddy::checkFriend($to_id)){
            throw new BadRequestHttpException("该用户还不是好友");
        }

        if(trim($params['message']) == ''){
            throw new BadRequestHttpException("不能发送空消息");
        }

        $whole = $params['message'];
        $item = null;
        while(True){
            $message = mb_substr($whole, 0, 500);
            if(!$message){
                break;
            }
            $item = UserBuddyMessage::createMessage($to_id, $message);
            Yii::$app->eventHandler->send(AppEvent::EVENT_MESSAGE_SENDED, $item);
            $whole = mb_substr($whole, 500);
        }

        return Util::success();
    }

    function actionDelete($id){
        $item = UserBuddyMessage::findOneOrError($id);
        $item->status = Util::STATUS_DISABLE;
        $item->save();
        Yii::$app->eventHandler->send(AppEvent::EVENT_MESSAGE_DELETED, $item);
        return Util::success();
    }
}

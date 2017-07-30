<?php

namespace app\api\common\models;

use Yii;
use yii\web\BadRequestHttpException;

/**
 * This is the model class for table "user_buddy_message".
 *
 * @property integer $id
 * @property integer $from_id
 * @property integer $to_id
 * @property string $message
 * @property string $status
 * @property string $created_at
 */
class UserBuddyMessage extends \app\api\common\util\DataRecord
{

    static $format_name;
    static function getFormat(){
        return [
            'default'=>[['message']]
        ];
    }

    public function fields(){
        $f = parent::fields();
        $extra = [];
        $extra['message'] = function(){
            if($this->status == Util::STATUS_DISABLE){
                return "[该条记录已删除]";
            }
            return $this->message;
        };

        $f = self::format($f, $extra);
        return $f;
    }
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_buddy_message';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['from_id', 'to_id'], 'integer'],
            [['created_at'], 'safe'],
            [['message'], 'string', 'max' => 500],
            [['status'], 'string', 'max' => 20],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'buddy_id' => 'Buddy ID',
            'message' => 'Message',
            'created_at' => 'Created At',
        ];
    }

    static function createMessage($to_id, $message){
        $from_id = Yii::$app->user->id;
        $item = new self();
        $item->load([
            'from_id'=>$from_id,
            'to_id'=>$to_id,
            'message'=>$message
        ], '');
        $item->save();
        return $item;
    }

    static function queryByBuddy($params){
        $user2_id = intval($params['to_id']);
        $history = $params['history'];
        $user1_id = Yii::$app->user->id;
        if(!UserBuddy::checkFriend($user2_id)){
            throw new BadRequestHttpException("好友不存在");
        }
        $q = self::find();
        $q = $q->where([
            'or',
            ['and', ['from_id'=>$user1_id, 'to_id'=>$user2_id]],
            ['and', ['from_id'=>$user2_id, 'to_id'=>$user1_id]],
        ])->orderBy(['id'=>SORT_DESC]);
        if(!$history){
            $q->limit(20);
        }
        $items = $q->all();
        $items = array_reverse($items);
        return $items;
    }

    //处理消息发送后
    function sended(){
        //添加好友
        $buddy_item = UserBuddy::addFriend($this->to_id, $this->from_id);

        //增加对方的unread
        $buddy_item->increaseUnread();
        $buddy_item->setRefresh();
        $to_user = User::findOneOrError($this->to_id);
        $to_user->addUnread();
    }

    //处理消息发送后
    function deleted(){
        //添加好友
        $buddy_item = UserBuddy::info($this->to_id);
        $buddy_item->setRefresh();
    }
}

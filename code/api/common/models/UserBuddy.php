<?php

namespace app\api\common\models;

use Yii;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;

/**
 * This is the model class for table "user_buddy".
 *
 * @property integer $id
 * @property integer $user1_id
 * @property integer $user2_id
 * @property integer $user1_unread
 * @property integer $user2_unread
 * @property integer $user1_refresh
 * @property integer $user2_refresh
 * @property string $user1_status
 * @property string $user2_status
 * @property string $created_at
 */
class UserBuddy extends \app\api\common\util\DataRecord
{
    static $format_name;
    static function getFormat(){
        return [
            'default'=>[['status']]
        ];
    }

    private function getTag(){
        $user_id = Yii::$app->user->id;
        if($user_id == $this->user1_id){
            return 1;
        }
        if($user_id == $this->user2_id){
            return 2;
        }
        return 0;
    }

    private function getToTag(){
        return 3 - $this->getTag();
    }

    private function getFromTag(){
        return $this->getTag();
    }

    function myUnread(){
        $tag = $this->getFromTag();
        $variable = "user{$tag}_unread";
        return $this->$variable;
    }

    function myStatus(){
        $tag = $this->getTag();
        $variable = "user{$tag}_status";
        return $this->$variable;
    }

    public function fields(){
        $f = parent::fields();
        $extra = [];
        $extra['status'] = function(){
            return $this->myStatus();
        };

        $f = self::format($f, $extra);
        return $f;
    }
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_buddy';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user1_id', 'user2_id'], 'required'],
            [['user1_id', 'user2_id', 'user1_unread', 'user2_unread', 'user1_refresh', 'user2_refresh'], 'integer'],
            [['created_at', 'user1_status', 'user2_status'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user1_id' => 'From ID',
            'user2_id' => 'To ID',
            'created_at' => 'Created At',
        ];
    }

    static function addFriend($from_id, $to_id){
        if($from_id == $to_id){
            throw new BadRequestHttpException("不能添加自己为好友");
        }
        $enable = 'user1_status';
        if($from_id > $to_id){
            list($from_id, $to_id) = [$to_id, $from_id];
            $enable = 'user2_status';
        }

        $item = UserBuddy::fetch([
            'user1_id'=>$from_id,
            'user2_id'=>$to_id,
        ]);
        $item->$enable = Util::STATUS_ENABLE;
        $item->save();

        return $item;
    }

    static function info($buddy_id){
        $from_id = Yii::$app->user->id;
        $to_id = intval($buddy_id);
        if($from_id > $to_id){
            list($from_id, $to_id) = [$to_id, $from_id];
        }

        return self::find()->where([
            'user1_id'=>$from_id,
            'user2_id'=>$to_id,
        ])->one();
    }

    static function checkFriend($buddy_id){
        $info = self::info($buddy_id);
        if(!$info) return false;
        $info = $info->toArray();
        return $info['status'] == Util::STATUS_ENABLE;
    }

    static function checkNew(){
        $user_id = Yii::$app->user->id;
        $items = self::find()
            ->where([
                'or',
                ['and',
                    ['user1_id'=>$user_id],
                    ['user1_status'=>'enable'],
                    [
                        'or',
                        ['>', 'user1_unread', 0],
                        ['>', 'user1_refresh', 0]
                    ]
                ],
                ['and',
                    ['user2_id'=>$user_id],
                    ['user2_status'=>'enable'],
                    [
                        'or',
                        ['>', 'user2_unread', 0],
                        ['>', 'user2_refresh', 0]
                    ]
                ],
            ])->all();
        $ret = array_map(function($item){
            $tag = $item->getTag();
            $to_tag = $item->getToTag();
            $unread_v = "user{$tag}_unread";
            $user_v = "user{$to_tag}_id";
            $refresh_v = "user{$to_tag}_refresh";
            return [
                'unread'=>intval($item->$unread_v),
                'user_id'=>intval($item->$user_v),
                'refresh'=>intval($item->$refresh_v),
            ];
        }, $items);
        $unread_all = array_sum(ArrayHelper::getColumn($ret, 'unread'));

        $user = User::findOneOrError($user_id);
        return [
            'total'=>$user->unread,
            'detail'=>$ret,
            'unread_all'=>$unread_all
        ];
    }

    function increaseUnread(){
        $tag = $this->getToTag();
        $unread_v = "user{$tag}_unread";
        $this->$unread_v += 1;
        $this->save();
    }

    function clearUnread(){
        $tag = $this->getFromTag();
        $variable = "user{$tag}_unread";
        $this->$variable = 0;
        $refresh_v = "user{$tag}_refresh";
        $this->$refresh_v = 0;
        $this->save();
    }

    function setRefresh(){
        $tag = $this->getToTag();
        $refresh_v = "user{$tag}_refresh";
        $this->$refresh_v = 1;
        $this->save();
    }

    function disableBuddy(){
        $tag = $this->getFromTag();
        $v = "user{$tag}_status";
        $this->$v = Util::STATUS_DISABLE;
        $this->save();
        return $this;
    }
}

<?php

namespace app\api\common\models;

use app\api\common\util\DataRecord;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "user".
 *
 * @property integer $id
 * @property integer $unread
 * @property string $mail
 * @property string $password
 * @property string $created_at
 * @property string $updated_at
 */
class User extends DataRecord implements IdentityInterface
{
    static $format_name;
    static function getFormat(){
        return [
            'default'=>[[], ['password']],
            'buddy_list'=>[['unread'], ['password']],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['mail', 'password'], 'required'],
            [['created_at', 'updated_at'], 'safe'],
            [['mail'], 'string', 'max' => 200],
            [['password'], 'string', 'max' => 50],
            [['unread'], 'integer'],
            [['mail'], 'email', 'message'=>'邮箱格式不正确'],
            [['mail'], 'unique', 'targetClass' => '\app\api\common\models\User', 'message' => '邮箱名已存在.'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'mail' => 'Mail',
            'password' => 'Password',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
    public static function findIdentity($id)
    {
        return static::findOne($id);
        //return isset(self::$users[$id]) ? new static(self::$users[$id]) : null;
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token]);
    }
    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->authKey;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->authKey === $authKey;
    }

    static function edit($params, $id=null){
        if($id){
            $item = self::findOneOrError($id);
            unset($params['id']);
        } else {
            $item = new self();
        }
        if(isset($params['password'])){
            $params['password'] = self::encryptPassword($params['password']);
        }
        $item->load($params, '');
        $item->save();
    }

    static $salt = "v100xwslw";
    static function encryptPassword($password){
        return md5($password.self::$salt);
    }

    static function login($params){
        $item = self::findOneOrError(["mail"=>$params['mail']], "用户名不存在");
        if(self::encryptPassword($params['password']) != $item->password){
            throw new BadRequestHttpException("用户信息错误");
        }
        Yii::$app->user->login($item);
        return $item;
    }


    static function queryMyBuddy(){
        $user_id = intval(Yii::$app->user->id);
        $from_items = User::find()
            ->innerJoin('user_buddy as  ub1', "ub1.user1_id=user.id and ub1.user2_id={$user_id} and ub1.user2_status='enable'")->all();

        $to_items = User::find()
            ->innerJoin('user_buddy as  ub2', "ub2.user2_id=user.id and ub2.user1_id={$user_id} and ub2.user1_status='enable'")->all();

        return array_merge($from_items, $to_items);
    }

    public function fields(){
        $f = parent::fields();
        $extra = [];
        $extra['unread'] = function(){
            $buddy_info = UserBuddy::info($this->id);
            if(!$buddy_info) return 0;

            return $buddy_info->myUnread();
        };

        $f = self::format($f, $extra);
        return $f;
    }

    function clearUnread(){
        $this->unread = 0;
        $this->save();
    }

    function addUnread(){
        $this->unread = 1;
        $this->save();
    }
}

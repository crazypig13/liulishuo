<?php
namespace app\api\common\util;

use app\api\common\models\DataLog;
use app\api\common\models\Util;
use Yii;
use yii\base\InvalidParamException;
use yii\db\ActiveRecord;
use yii\helpers\VarDumper;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;

/**
 * Class DataRecord
 * @package app\api\common\util
 *
 * 1 validate to exception
 * 2 save DML writing operations to db.cv_data_log
 */
class DataRecord extends \yii\db\ActiveRecord
{
    const DB_LOG = false;
    const FIELD_MAX_LENGTH = 150;
    const RECORD_MAX_LENGTH = 1024;

    //将validate失败的情形转成异常
    public function afterValidate(){
        if(!$this->hasErrors()){
            return ;
        }
        Util::logRequest();
        Util::log(Util::errorStack());
        if(!defined('YII_CONSOLE')){
            throw new BadRequestHttpException('字段验证失败：'.self::formatValidationError($this));
        } else {
            throw new InvalidParamException('字段验证失败：'.self::formatValidationError($this));
        }
    }

    public function afterSave($insert, $old_attr){
        //区分插入/修改
        $action = 'update';
        if($insert){
            $action = 'insert';
        }
        $this->dataLog($action, $old_attr);
    }

    public function afterDelete(){
        $this->dataLog('delete', []);
    }

    public function dataLog($action, $old_attr){
        if(!self::DB_LOG){
            return;
        }
        $new_attr = [];
        foreach($old_attr as $k=>$v){
            $new_attr[$k] = $this->$k;
        }

        $params = [
            'obj_type'=>get_called_class(),
            'action'=>$action
        ];

        //区分控制台/WEB访问
        if(defined('YII_CONSOLE')){
            $params['opt_type'] = 'console';
        } else {
            $params['opt_type'] = 'web';
            if(Yii::$app->user->isGuest){
                $params['opt_user_role'] = 'GUEST';
                $params['opt_user_id'] = 0;
            } elseif(Yii::$app->user->can('admin')) {
                $params['opt_user_role'] = 'admin';
                $params['opt_user_id'] = Yii::$app->user->id;
            } elseif(Yii::$app->user->can('hr')) {
                $params['opt_user_role'] = 'hr';
                $params['opt_user_id'] = Yii::$app->user->id;
            } else {
                $params['opt_user_role'] = '';
                $params['opt_user_id'] = Yii::$app->user->id;
            }
        }

        //整理字段
        $params['obj_id'] = $this->primaryKey;
        array_walk($old_attr, function(&$val, $key){
            self::shortField($val, $key);
        });
        array_walk($new_attr, function(&$val, $key){
            self::shortField($val, $key);
        });
        $params['obj_before'] = self::shortRecord($old_attr);
        $params['obj_after'] = self::shortRecord($new_attr);

        //记数据库日志
        $log = new DataLog();
        $log->load($params, '');
        if(!$log->save()){
            error_log('log failed');
        }
    }

    /**
     * @param $condition
     * @param string $msg
     * @return static
     * @throws NotFoundHttpException
     */
    public static function findOneOrError($condition, $msg = ""){
        $obj = self::findOne($condition);
        if($obj){
            return $obj;
        } else {
            Util::logRequest();
            Util::log(Util::errorStack());
            if($msg){
                throw new NotFoundHttpException($msg);
            } else {
                throw new NotFoundHttpException(self::className()." obj not found");
            }
        }
    }

    static function shortField(&$val, $key){
        if(is_string($val) && strlen($val) > self::FIELD_MAX_LENGTH){
            $val = substr($val, 0, self::FIELD_MAX_LENGTH);
        }
    }

    static function shortRecord($attr){
        $attr_str = VarDumper::dumpAsString($attr);
        if(strlen($attr_str) > self::RECORD_MAX_LENGTH){
            $attr_str = substr($attr_str, 0, self::RECORD_MAX_LENGTH);
        }
        return $attr_str;
    }

    //格式化供前端展示
    static function formatValidationError(ActiveRecord $model){
        $ret = [];
        foreach($model->getFirstErrors() as $k=>$v){
            $ret[] = "[$k:$v]";
        }
        return implode('', $ret);
    }

    static function setFormat($name){
        static::$format_name = $name;
    }
    static function format($fields, $extra){
        $map = static::getFormat();
        if(!$map[static::$format_name]){
            $format = $map['default'];
        } else {
            $format = $map[static::$format_name];
        }
        $include = $format;
        $exclude = [];
        if(is_array($format[0])){
            $include = $format[0];
            $exclude = $format[1];
        }
        if($include){
            foreach($extra as $k=>$v){
                if(in_array($k, $include)){
                    $fields[$k] = $v;
                }
            }
        }
        if($exclude){
//            error_log(print_r($exclude, true));
            foreach($fields as $k=>$v){
                if(in_array($k, $exclude)){
                    unset($fields[$k]);
                }
            }
        }
        return $fields;
    }

    /**
     * @return static
     */
    static function fetch($keys, $vals=[]){
        $item = self::findOne($keys);
        if(!$item){
            $item = new static();       //这里后期绑定为创建子类对象
            $p = array_merge($keys, $vals);
            $item->load($p, '');
            $item->save();
        }
        return $item;
    }

    //返回 [(是否新条目), 条目]
    static function fetchEx($keys, $vals=[]){
        $is_new = 0;
        $item = self::findOne($keys);
        if(!$item){
            $item = new static();       //这里后期绑定为创建子类对象
            $is_new = 1;
            $p = array_merge($keys, $vals);
            $item->load($p, '');
            $item->save();
        }
        return [$is_new, $item];
    }
}

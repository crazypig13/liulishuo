<?php

namespace app\api\common\models;

use yii\db\Exception;
use yii\helpers\ArrayHelper;
use yii\helpers\VarDumper;

class Util
{
    const STATUS_ENABLE = 'enable';
    const STATUS_DISABLE = 'disable';

    static function success($message='ok'){
        return [
            'message'=>$message
        ];
    }

    static function selectDb($db){
        \Yii::$app->db->createCommand("use {$db}")->execute();
    }

    static function getNow(){
        return date("Y-m-d H:i:s");
    }

    static function log($log){
        $ip = \Yii::$app->request->userIP;
        $log = self::paramToStr($log);
        error_log("[{$ip}]$log");
    }

    static function paramToStr($p, $length=0){
        if(is_object($p)){
            $p = VarDumper::dumpAsString($p);
        } elseif (!is_string($p)){
            $p = json_encode($p, JSON_PRETTY_PRINT);
        }
        if($length){
            $p = substr($p, 0, $length);
        }
        return $p;
    }

    static function errorStack(){
        $traces = debug_backtrace();
        $logs = array_map(function($trace){
            return [
                'file'=>$trace['file'],
                'line'=>$trace['line'],
                'function'=>$trace['function'],
                'class'=>$trace['class'],
            ];
        }, $traces);
        return $logs;
    }

    static function notNullValue($arr){
        foreach($arr as $val){
            if(!is_null($val)){
                return $val;
            }
        }
        return null;
    }

    static function checkValue($val, $error)
    {
        if ($val) {
            return true;
        } else {
            if ($error instanceof Exception) {
                throw $error;
            } else {
                throw new \yii\base\Exception($val);
            }
        }
    }

    /**
     * 对象转换成数组
     * @param $array
     * @return array
     */
    static function object_array($array)
    {
        if (is_object($array)) {
            $array = (array)$array;
        }
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                $array[$key] = self::object_array($value);
            }
        }
        return $array;
    }

    static function logRequest(){
        $item = [
            'ip'=>$_SERVER['REMOTE_ADDR'],
            'url'=>$_SERVER['REQUEST_URI'],
            'get'=>$_GET,
            'post'=>$_POST,
            'user_id'=>\Yii::$app->user->id
        ];

        $item['post_raw'] = \Yii::$app->request->rawBody;

        self::log($item);
    }
}
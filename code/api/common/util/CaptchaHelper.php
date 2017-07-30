<?php
namespace app\api\common\util;

use Yii;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Class CaptchaHelper
 * @package app\api\common\util
 * 依赖Yii::Session  CaptchaCode
 */
class CaptchaHelper{
    const SESSION_CAPTCHA = '_check_captcha_';

    /**
     * @param null $need
     * @return bool
     * 设置是否需要检查验证码
     */
    function needCheck($need = null){
        $key = '_need_check_captcha';
        $session_need = (bool)(Yii::$app->session->get($key));
        if($need === null){
            return $session_need;
        } else {
            $need = (bool)($need);
            Yii::$app->session->set($key, $need);
            return $need;
        }
    }

    /**
     * 返回验证码图片，action中调用后，不应再有其他逻辑
     */
    function img(){
        Yii::$app->response->format = Response::FORMAT_RAW;
        $cc = new CaptchaCode();
        $cc->doimg();

        $this->needCheck(true);
        Yii::$app->session->set(self::SESSION_CAPTCHA, $cc->getCode());
    }


    /**
     * @param null $code
     * @throws NotFoundHttpException
     * 检查验证码
     */
    function checkCode($code = null){
        if($code === null){
            $params = Yii::$app->request->bodyParams;
            $code = $params['captcha'];
        }
        $session_captcha = Yii::$app->session->get(self::SESSION_CAPTCHA);
        if(strtoupper($session_captcha) != strtoupper($code) || !$session_captcha){
            return false;
        }
        return true;
    }
}
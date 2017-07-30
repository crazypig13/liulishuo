<?
namespace app\api\common\util;

use yii\web\ServerErrorHttpException;

class SmsHelper{
    private $tpl;
    private $app_id;
    private $app_key;

    /**
     * @param mixed $app_id
     */
    public function setAppId($app_id)
    {
        $this->app_id = $app_id;
    }

    /**
     * @param mixed $app_key
     */
    public function setAppKey($app_key)
    {
        $this->app_key = $app_key;
    }

    /**
     * @param mixed $tpl
     */
    public function setTpl($tpl)
    {
        $this->tpl = $tpl;
    }

    function sendMessage($mobile, $params){
        $message = vsprintf($this->tpl, $params);
        $rand = "". rand(10000, 99999) . rand(10000, 99999);
        $now = time();
        $p = [
            'tel'=>[
                'nationcode'=> '86',
                'mobile'=> $mobile,
            ],
            'type'=>0,
            'msg'=>$message,
            'sig'=>$this->sign($mobile, $rand, $now),
            'time'=>$now,
        ];

        $url = "https://yun.tim.qq.com/v5/tlssmssvr/sendsms?sdkappid={$this->app_id}&random={$rand}";

        list($_, $_, $ret) = HttpClient::postJson($url, $p);
        if($ret['result'] != 0){
            throw new ServerErrorHttpException("短信发送失败：{$ret['errmsg']}");
        }
        return true;
    }

    function sign($mobile, $rand, $now){
        $sign_tpl = "appkey={$this->app_key}&random={$rand}&time={$now}&mobile={$mobile}";
        $sign = hash('sha256', $sign_tpl);
        return $sign;
    }
}
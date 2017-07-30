<?
namespace app\api\common\components;

use app\api\common\models\OpLog;
use Yii;
use yii\web\ServerErrorHttpException;
use yii\web\UnauthorizedHttpException;

class CommonHelper{
    /**
     * 根据图片数据、图片类型、公司url名、图片字段，保存图片，并生成图片链接
     *
     * @param $data  base64的数据
     * @param $type  图片后缀
     * @param $company  公司名
     * @return string 返回URl
     */
    static function saveImage($data, $type, $company, $img_name=null){
        if(!$img_name){
            $img_name = md5($data);
        }
        if(!$company){
            OpLog::log("文件保存失败：{$company} 参数错误", "Common_Helper");
            throw new ServerErrorHttpException("文件保存失败：{$company} 参数错误");
        }

        if(!$data || !$type){
            return '';
        }

        $uri = "/resources/{$company}/{$img_name}.{$type}";
        $path = Yii::getAlias('@webroot').$uri;
        $path_dir = dirname($path);
        if(!is_dir($path_dir)){
            mkdir($path_dir);
        }
        file_put_contents($path, base64_decode($data));

        return $uri;
    }

    //将主题色和边框色保存到公司css
    static function saveCss($theme_color, $border_color, $company){
        $tpl = Yii::getAlias('@app')."/template/theme.css";
        $cont = file_get_contents($tpl);
        $cont = sprintf($cont, $theme_color, $border_color);

        $css_uri = "/resources/{$company}/theme.css";
        $css = Yii::getAlias('@webroot').$css_uri;

        file_put_contents($css, $cont);
    }

    static function binToList($bin, $arr)
    {
        $ret = [];
        foreach ($arr as $k => $v) {
            if (($bin & (1<<$k)) > 0) {
                $ret[] = $v;
            }
        }
        return $ret;
    }

    static function getSessionUserId(){
        if(defined('YII_CONSOLE')){
            return 0;
        }

        $session = Yii::$app->session;

        $company_name = Yii::$app->params['company'];
        if (!$session->isActive) {
            $session->open();
        }

        if (!is_array($session[$company_name]) || empty($session[$company_name]['user_id'])) {

            /**************正式环境 抛异常**************/
            if (defined('YII_ENV') and YII_ENV == 'prod') {
//                throw new UnauthorizedHttpException(Yii::t('yii', '当前用户尚未登录.'));
                return 0;
            }
            $user_id = 1;
            /*******************************************/

        } else {
            $user_id = $session[$company_name]['user_id'];
        }

        return $user_id;
    }

    static function template($str, $dict){
        extract($dict);
        eval("\$str = \"$str\";");
        return $str;
    }

}
<?
namespace app\api\common\util;

use app\controllers\BaseController;
use Yii;
use yii\helpers\Inflector;
use yii\helpers\VarDumper;
use yii\web\UrlRule;

class ApiUrlRule extends UrlRule{

    /*
     * 处理api中 module1/module2/controller/[action[/id]|id] 形式的url
     * @todo 缓存路由
     */
    public function parseRequest($manager, $request)
    {
        $path_info = $request->getPathInfo();
        $method = $request->getMethod();

        if(!$path_info){
            return false;
        }

        $pos = 0;
        $api_dir = Yii::getAlias('@app')."/";
        while(true){
            if($pos === false){
                break;
            }
            $pos = strpos($path_info, '/', $pos+1);
            if($pos === false){
                $current_path = $path_info;
            } else {
                $current_path = substr($path_info, 0, $pos);
            }

            //首先确定 a/b/c 是否为目录，如果为目录继续向前
            if(is_dir("{$api_dir}/{$current_path}")){
                continue;
            }

            //如果不为目录，试图取controller（兼容复数形式）
            $controller = Yii::$app->createController($current_path);
            if(!$controller){
                $current_path = Inflector::singularize($current_path);
                $controller = Yii::$app->createController($current_path);
            }
            if(!$controller){
                //取不到controller时认为匹配失败
                return false;
            } else {
                $controller = $controller[0];
            }

            $action_id = $param = null;
            //取完controller后，判断pathinfo里剩余部分。 如 a/b/c/d/e， a/b/c为controller， 此处继续处理 d/e
            if($pos !== false){
                //如果存在剩余部分， 尝试取action
                $action_param = substr($path_info, $pos + 1);
                $action_pos = strpos($action_param, '/');
                if($action_pos !== false){
                    $action_id = substr($action_param, 0, $action_pos);
                    $param = substr($action_param, $action_pos + 1);
                } else {
                    $action_id = $action_param;
                }
                $action = $controller->createAction($action_id);
                if(!$action){
                    $action_id = null;
                }
            }

            //如果action没有显式指定，根据method和param确定action（restful方式）
            if(!$action_id){
                $action_id = 'index';
                if($pos !== false){
                    $param = substr($path_info, $pos + 1);
                }
                if($param){
                    if($method == 'PUT' || $method == 'POST'){
                        $action_id = 'update';
                    } elseif($method == 'DELETE') {
                        $action_id = 'delete';
                    } else {
                        $action_id = 'view';
                    }
                } else {
                    if($method == 'POST'){
                        $action_id = 'create';
                    }
                }
            }

            if($method == 'OPTIONS'){
                $action_id = 'options';
            }

            //controller+action得到最终的route
            $current_path .= "/".$action_id;

            //pathinfo剩余部分处理为param
            if($param){
                $param = ['id'=>$param];
            } else {
                $param = [];
            }
            return [$current_path, $param];
        }

        return false;
    }
}
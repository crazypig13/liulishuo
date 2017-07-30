<?
namespace app\api\super\components;

use Yii;
use yii\base\ActionFilter;
use yii\base\Exception;
use yii\web\Session;
use yii\web\UnauthorizedHttpException;
use yii\web\Response;

class AccessFilter extends ActionFilter
{
    public function beforeAction($action)
    {
        $session = new Session();
        $session->open();
        $admin = $session['super']['admin'];
        if(!$admin){
            Yii::$app->response->format = Response::FORMAT_JSON;
            throw new UnauthorizedHttpException("用户尚未登录");
        }
        return parent::beforeAction($action);
    }

    public function afterAction($action, $result)
    {
        return parent::afterAction($action, $result);
    }
}
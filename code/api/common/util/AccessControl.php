<?
namespace app\api\common\util;

use Yii;
use yii\helpers\VarDumper;
use yii\web\Session;
use yii\web\UnauthorizedHttpException;

class AccessControl extends \yii\filters\AccessControl
{
    public $ruleConfig = ['class' => 'app\api\common\util\AccessRule'];

    public function beforeAction($action)
    {
        return parent::beforeAction($action);
    }

    public function afterAction($action, $result)
    {
        return parent::afterAction($action, $result);
    }

}
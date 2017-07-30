<?
namespace app\api\common\util;

use app\api\common\models\Headhunter;
use app\api\common\models\OpLog;
use app\api\common\models\User;
use app\api\common\models\pineapple\Company;
use Yii;
use yii\web\ServerErrorHttpException;

class SessionInfo{
    //已登录的猎头id
    static function getHeadHunterId(){
        if (defined('YII_ENV') and YII_ENV == 'dev') {
            return 2;
        }
        return Yii::$app->user->id;
    }

    //已登录的猎头的信息
    static function getHeadHunter(){
        $id = self::getHeadHunterId();
        if($id){
            return Headhunter::findOne($id);
        }
    }
    //已登录的猎头id
    static function getHrId(){
        if (defined('YII_ENV') and YII_ENV == 'dev') {
            return 1;
        }
        return Yii::$app->user->id;
    }

    //已登录的猎头的信息
    static function getHr(){
        $id = self::getHrId();
        if($id){
            return User::findOne($id);
        }
    }

    static function getCompany(){
        $name = Yii::$app->params['company'];
        $company = Company::find()->where(['=', 'url_name', $name])->one();
        return $company;
    }
}
<?

namespace app\api\common\custom;

use app\api\common\models\Feed;
use app\api\common\models\User;
use Yii;
use app\api\app\controllers\MkPointController;
use app\api\common\components\AppEvent;
use yii\helpers\VarDumper;

class DefaultAppEvent extends AppEvent{
    function init(){
        $this->on(self::EVENT_LOGIN, function($event){
        });

        $this->on(self::EVENT_MESSAGE_SENDED, function($event){
            $message = $event->message;
            $message->sended();
        });

        $this->on(self::EVENT_MESSAGE_DELETED, function($event){
            $message = $event->message;
            $message->deleted();
        });

        $this->on(self::EVENT_BUDDY_READED, function($event){
            $buddy = $event->message;
            $buddy->clearUnread();
        });

        $this->on(self::EVENT_USER_READED, function($event){
            $user = User::findOneOrError(Yii::$app->user->id);
            $user->clearUnread();
        });
    }
}
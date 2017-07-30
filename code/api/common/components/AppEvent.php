<?
namespace app\api\common\components;

use Yii;
use yii\base\Component;
use yii\base\Event;
use yii\helpers\VarDumper;

class EventMessage extends Event{
    var $message;
}

class AppEvent extends Component{
    const EVENT_LOGIN = 'login';
    const EVENT_MESSAGE_SENDED = 'message_sended';
    const EVENT_MESSAGE_DELETED = 'message_deleted';
    const EVENT_BUDDY_READED = 'buddy_readed';
    const EVENT_USER_READED = 'user_readed';

    function send($event_name, $data=null){
        $event = new EventMessage();
        $event->message = $data;
        $this->trigger($event_name, $event);
    }
}
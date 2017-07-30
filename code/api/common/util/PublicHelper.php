<?
namespace app\api\common\util;

use app\api\common\models\OpLog;
use app\api\common\models\User;
use Yii;
use yii\helpers\VarDumper;
use yii\web\BadRequestHttpException;
use yii\web\ServerErrorHttpException;

class PublicHelper
{
    const AVAILABLE_USER_IDS = 'available_user_ids';

    //传入GET参数，协议好_page_size和_page参数
    static function getPagination($params)
    {
        $pageSize = 20;
        if (isset($params['_page_size']) && $params['_page_size'] == -1) {
            $pagination = false;
        } else {
            isset($params['_page']) ? $page = $params['_page'] : $page = 1;
            if (isset($params['_page_size']) && $params['_page_size'] > 0) {
                $pageSize = $params['_page_size'];
            }
            $pagination = [
                'pageSize' => $pageSize,
                'page' => $page - 1,
            ];
        }
        return $pagination;
    }

    //传入pagination结构和query， 返回meta结构
    static function getPaginationMeta($pagination, $query)
    {
        $count_query = clone $query;

        $_meta = [];
        $_meta['totalCount'] = $count_query->count();
        if ($pagination) {
            $_meta['perPage'] = $pagination['pageSize'];
            $_meta['currentPage'] = $pagination['page'] + 1;
            $_meta['pageCount'] = ceil($_meta['totalCount'] / $_meta['perPage']);
        }
        return $_meta;
    }

    /**
     * @param $pagination
     * @param ActiveQuery
     * @return ActiveQuery
     */
    static function getPaginationQuery($pagination, $query)
    {
        if (!$pagination) {
            return $query;
        }
        $query->offset($pagination['page'] * $pagination['pageSize'])
            ->limit($pagination['pageSize']);
        return $query;
    }

    static function setMetaHeader($meta)
    {
        if ($meta) {
            Yii::$app->response->headers->set('X-Meta-List', json_encode($meta));
        }
    }

    //前端不报错
    static function setAlarmHeader($alarm = 0)
    {
        Yii::$app->response->headers->set('alarm', $alarm);
    }

    static function checkAdmin()
    {
        if (!self::isAdmin()) {
            throw new BadRequestHttpException('无此权限访问');
        }
    }

    static function isAdmin()
    {
        return Yii::$app->user->can('admin');
    }


    static function getSectionPagination($params)
    {
        $pageSize = 20;
        $sectionSize = 5;
        if (isset($params['_page_size']) && $params['_page_size'] == -1) {
            $pagination = false;
        } else {
            isset($params['_page']) ? $page = $params['_page'] : $page = 1;
            if (isset($params['_page_size']) && $params['_page_size'] > 0) {
                $pageSize = $params['_page_size'];
            }
            if (isset($params['_section_size']) && $params['_section_size'] > 0) {
                $sectionSize = $params['_section_size'];
            }
            $pagination = [
                'pageSize' => $pageSize,
                'page' => $page - 1,
                'sectionSize' => $sectionSize,
            ];
        }
        return $pagination;
    }

    //传入pagination结构和query， 返回meta结构
    static function getSectionPaginationMeta($pagination, $query)
    {
        $count_query = clone $query;

        $_meta = [];
        $_meta['totalCount'] = $count_query->count();
        if ($pagination) {
            $_meta['perPage'] = $pagination['pageSize'];
            $_meta['currentPage'] = $pagination['page'] + 1;
            $_meta['pageCount'] = ceil($_meta['totalCount'] / $_meta['perPage']);

            //根据sectionSize 额外计算section信息
            $_meta['perSection'] = $pagination['sectionSize'];
            $_meta['sectionCount'] = ceil($_meta['pageCount'] / $_meta['perSection']);
            $_meta['currentSection'] = ceil($_meta['currentPage'] / $_meta['perSection']);
        }
        return $_meta;
    }

    /**
     * @param $pagination
     * @param ActiveQuery
     * @return mixed
     */
    static function getSectionPaginationQuery($pagination, $query)
    {
        if (!$pagination) {
            return $query;
        }
        $offset = $pagination['page'] * $pagination['pageSize'];

        $query->offset($offset)
            ->limit($pagination['pageSize']);
        return $query;
    }

    static function getQiyeErrorMessage($code, $defalut="未找到错误信息"){
        $file = Yii::getAlias('@app')."/config/qiye_error_code.txt";
        $content = file_get_contents($file);
        if($content){
            $lines = explode("\n", $content);
            $codes = [];
            foreach($lines as $line){
                $tmp = explode("\t", $line);
                $codes[trim($tmp[0])] = trim($tmp[1]);
            }
            $msg = $codes[$code];
            if($msg){
                return $msg;
            }
        }
        return $defalut."[$code]";
    }
}
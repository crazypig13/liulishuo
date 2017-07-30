<?
namespace app\api\common\util;

use Yii;
use yii\web\ServerErrorHttpException;

class FileManager{
    var $type = 'default';
    const WITH_DATE = true;

    function __construct($type, $with_date = false){
        $corp_id = 'webapp';
        $custom = 'dev';
        $this->type = basename($type);
        $data_dir = "data/{$corp_id}/{$custom}/{$this->type}";

        if($with_date){
            $date = date('Ymd');
            $data_dir .= "/{$date}";
        }

        $this->data_dir = $data_dir;
    }

    function getPath($file_name){
        $ext = pathinfo($file_name, PATHINFO_EXTENSION );
        $path = $this->data_dir."/".uniqid().".".$ext;
        return $path;
    }

    function getRealPath($path){
        $real_path = Yii::getAlias('@app')."/".$path;

        if(!is_dir(dirname($real_path))){
            mkdir(dirname($real_path), 0777, true);
        }
        return $real_path;
    }

    //拷贝文件
    function copy($file){
        $path = $this->getPath($file);
        $real_path = $this->getRealPath($path);
        copy($file, $real_path);
        return [
            'name'=>$file,
            'path'=>$path
        ];
    }

    //保存文件内容
    function save($content, $file){
        $path = $this->getPath($file);
        $real_path = $this->getRealPath($path);
        file_put_contents($real_path, $content);
        return [
            'name'=>$this->basename($file),
            'path'=>$path
        ];
    }

    function getRealStaticPath($path){
        $real_path = Yii::getAlias('@webroot').$path;

        if(!is_dir(dirname($real_path))){
            mkdir(dirname($real_path), 0777, true);
        }
        return $real_path;
    }

    function getStaticPath($file_name, $path){
        $ext = pathinfo($file_name, PATHINFO_EXTENSION );
        $path = '/'.trim($path, '/');
        $path = $path."/".uniqid().".".$ext;
        return $path;
    }

    //处理上传的文件
    function upload($f, $option=[]){
        if(!$option['static_path']){
            $path = $this->getPath($f['name']);
            $real_path = $this->getRealPath($path);
        } else {
            $path = $this->getStaticPath($f['name'], $option['static_path']);
            $real_path = $this->getRealStaticPath($path);
        }

        if(!move_uploaded_file($f['tmp_name'], $real_path)){
            throw new ServerErrorHttpException('文件上传处理失败：'.$f['name']);
        }
        return [
            'name'=>$f['name'],
            'path'=>$path
        ];
    }

    static function uploadStatic($file, $static_path){
        $fm = new FileManager('static');
        return $fm->upload($file, ['static_path'=>$static_path]);
    }

    static function uploadMobile($file){
        $fm = new FileManager('import_mobile');
        return $fm->upload($file);
    }

    static function uploadCarouselImage($file){
        $fm = new FileManager('carousel');
        return $fm->upload($file);
    }

    static function uploadNoticeCover($file){
        $fm = new FileManager('notiv');
        return $fm->upload($file);
    }

    static function saveFeedImage($content, $file){
        $fm = new FileManager('feed', self::WITH_DATE);
        return $fm->save($content, $file);
    }

    static function saveCarouselImage($content, $file){
        $fm = new FileManager('carousel', self::WITH_DATE);
        return $fm->save($content, $file);
    }

    static function copyFeedImage($file){
        $fm = new FileManager('feed', self::WITH_DATE);
        return $fm->copy($file);
    }

    static function saveAvatarImage($content, $file){
        $fm = new FileManager('avatar');
        return $fm->save($content, $file);
    }

    static function basename($name){
        return end(explode('/', $name));
    }
}
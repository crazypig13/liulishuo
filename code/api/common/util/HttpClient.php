<?
namespace app\api\common\util;

use Yii;
use yii\helpers\VarDumper;

/*
 * request:
 * get
 * get with params
 * post with []
 * post with json
 * post file
 * post multiple file and params
 *
 * response:
 * no need response
 * json format
 * download file
 *
 * error: [status_code and rsp body]
 * false (fetch error then)
 * throw Exception with status_code
 *
 * log:
 *
 */

/*
 *
 *  return HttpClient::get($url);
    return HttpClient::get($url, ['a'=>'n', 'c'=>'http://www.163.com?c=1&d=2#!xbwp']);
    return HttpClient::post($url, ['a'=>'n', 'c'=>'http://www.163.com?c=1&d=2#!xbwp']);
    return HttpClient::postFile($url, '/home/wwwroot/pineapple/web/code/README.md');
    return HttpClient::postFiles($url, ['a'=>'n', 'c'=>'http://www.163.com?c=1&d=2#!xbwp'],
        [
            ['path'=>'/home/wwwroot/pineapple/web/code/README.md','name'=>'中文.md'],
            ['path'=>'/home/wwwroot/pineapple/web/code/LICENSE.md','name'=>'许可证.md'],
        ]
    );
 */

class HttpClient{
    const REQ_METHOD_GET = 'GET';
    const REQ_METHOD_POST = 'POST';

    const REQ_FORMAT_JSON = 'JSON';
    const REQ_FORMAT_FORM = 'FORM';
    const REQ_FORMAT_FILE = 'FILE';
    const REQ_FORMAT_MULTIPLE_FILE = 'MULTIPLE_FILE';


    const RSP_FORMAT_JSON = 'JSON';
    const RSP_FORMAT_FILE = 'FILE';
    const RSP_FORMAT_DEFAULT = 'TEXT';

    const HTTP_OK = 200;

    var $option_req_method = self::REQ_METHOD_GET;
    var $option_req_format = self::REQ_FORMAT_FORM;
    var $option_rsp_format = self::RSP_FORMAT_DEFAULT;

    var $headers = [];
    var $body = '';
    var $is_file = 0;
    var $tmp_file;
    var $file_name = '';

    static function getJson($url, $params=null){
        $handler = new HttpClient();
        $handler->option_rsp_format = self::RSP_FORMAT_JSON;
        return $handler->curl($url, $params);
    }

    static function postJson($url, $params=null){
        $handler = new HttpClient();
        $handler->option_req_method = self::REQ_METHOD_POST;
        $handler->option_req_format = self::REQ_FORMAT_JSON;
        $handler->option_rsp_format = self::RSP_FORMAT_JSON;
        return $handler->curl($url, $params);
    }

    static function get($url, $params=[]){
        $handler = new HttpClient();
        return $handler->curl($url, $params);
    }

    static function post($url, $params){
        $handler = new HttpClient();
        $handler->option_req_method = self::REQ_METHOD_POST;
        return $handler->curl($url, $params);
    }

    static function postFile($url, $file){
        $handler = new HttpClient();
        $handler->option_req_method = self::REQ_METHOD_POST;
        return $handler->curl($url, null, $file);
    }

    static function postFiles($url, $params, $files){
        $handler = new HttpClient();
        $handler->option_req_method = self::REQ_METHOD_POST;
        return $handler->curl($url, $params, $files);
    }

    function curl($url, $params=null, $files=null){
        $files = self::parseReqFiles($files);
        $curl = curl_init();
        if(stripos(trim($url),"https://") === 0){
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($curl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
        }
        $req_headers = [];

        if($this->option_req_format == self::REQ_FORMAT_JSON){
            $req_headers[] = 'Content-Type:application/json';
        }

        if($this->option_req_method == self::REQ_METHOD_GET){
            if($params){
                $url = self::appendToUrl($url, $params);
            }
        } else {
            curl_setopt($curl, CURLOPT_POST, 1);
            if($this->option_req_format == self::REQ_FORMAT_JSON){
                $params = json_encode($params, JSON_UNESCAPED_UNICODE);
            } else {
                if(!$params){
                    $params = [];
                }
                if($files){
                    foreach($files as $key=>$file){
                        $params["_files_{$key}_"] = '@'.$file['path'].';filename='.$file['name'];
                    }
                }
            }
            curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
        }

        curl_setopt($curl, CURLOPT_URL, $url);
        if($req_headers){
            curl_setopt($curl, CURLOPT_HTTPHEADER, $req_headers);
        }

        //初始化可能下载的文件名
        $this->file_name = self::tryGetFileNameFromUrl($url);
        $ext = pathinfo($this->file_name, PATHINFO_EXTENSION );
        $this->tmp_file = "/tmp/".uniqid().".".$ext;

        //处理头
        curl_setopt($curl, CURLOPT_HEADERFUNCTION,
            function($curl, $str) use ($url){
                $tmp = explode(':', $str);
                if(count($tmp) > 1){
                    $this->headers[trim($tmp[0])] = trim($tmp[1]);
                }
                if(stripos($tmp[0], 'Content-Disposition') === 0){
                    $regex = "/filename=(.*)/";
                    $m = preg_match($regex, trim($tmp[1]), $out);
                    if($m){
                        $this->file_name = trim($out[1], '\'"\\');
                        $ext = pathinfo($this->file_name, PATHINFO_EXTENSION );
                        $this->tmp_file = "/tmp/".uniqid().".".$ext;
                    }
                    $this->option_rsp_format = self::REQ_FORMAT_FILE;
                }
                return strlen($str);
            });
        //处理body
        curl_setopt($curl, CURLOPT_WRITEFUNCTION,
            function($curl, $str){
                if($this->option_rsp_format == self::REQ_FORMAT_FILE){
                    file_put_contents($this->tmp_file, $str, FILE_APPEND);
                } else {
                    $this->body .= $str;
                }
                return strlen($str);
            });

        //正常返回
        $ret = curl_exec($curl);
        $status = curl_getinfo($curl);
        if(($error_no = curl_errno($curl))){
            return [$ret, $error_no, curl_error($curl)];
        }
        curl_close($curl);

        if(stripos($status['content_type'], 'application/json') !== false || $this->option_rsp_format == self::RSP_FORMAT_JSON){
            $this->body = json_decode($this->body, true);
        }


        if($this->option_rsp_format == self::REQ_FORMAT_FILE){
            return [$status['http_code'], $this->headers, $this->file_name, $this->tmp_file];
        }

        return [$status['http_code'], $this->headers, $this->body];
    }

    //form [a=>b, c=>d] 转为 a=b&c=d 形式
    private static function parseForm($params){
        if(!is_array($params)){
            return $params;
        }
        $tmp = [];
        foreach($params as $k=>$v){
            $tmp[] = "{$k}=".urlencode($v);
        }
        return implode('&', $tmp);
    }

    //url上追加get参数
    private static function appendToUrl($url, $params){
        if(!$params){
            return $url;
        }
        $params = self::parseForm($params);
        $mark = strpos($url, '?');
        if($mark === false){
            return $url.'?'.$params;
        }
        if($mark == strlen($url) - 1){
            //在行尾
            return $url.$params;
        }
        return $url.'&'.$params;
    }

    private static function tryGetFileNameFromUrl($url){
        $tmp = explode("/", $url);
        $name = $tmp[count($tmp) - 1];
        $tmp = explode('?', $name);
        $name = $tmp[0];
        return $name;
    }

    private static function parseReqFiles($files){
        if(!$files){
            return null;
        }
        if(!is_array($files)){
            $files = [
                'path'=>$files,
                'name'=>basename($files)
            ];
        }
        if(!$files[0]){
            $files = [$files];
        }
        return $files;
    }
}

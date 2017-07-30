<?php
/**
 * Created by PhpStorm.
 * User: Xuli
 * Date: 2015/12/21
 * Time: 10:15
 */

namespace app\api\common\util;

use Yii;


class AccessRule extends \yii\filters\AccessRule
{
    /**
     * @param Controller $controller the controller
     * @return boolean whether the rule applies to the controller
     */
    protected function matchController($controller)
    {
        $package = [];
        if(empty($this->controllers)){
            return true;
        }
        foreach($this->controllers as $key => $ctrl) {
            $package[$key] = 'admin/' . $ctrl;
        }

        return empty($this->controllers) || in_array($controller->uniqueId, $package, true);
    }

}
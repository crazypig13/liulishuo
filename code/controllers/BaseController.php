<?php

namespace app\controllers;

use Yii;
use yii\base\ActionEvent;
use yii\web\Controller;
use yii\web\MethodNotAllowedHttpException;

class BaseController extends Controller
{
    function init()
    {
        parent::init();
    }

    function afterAction($action, $result)
    {
        echo json_encode($result, JSON_PRETTY_PRINT);
    }

    /**
     * @param ActionEvent $event
     * @return boolen
     * @throws MethodNotAllowedHttpException
     */
    function beforeAction($event)
    {
        $action = $event->id;

        if (isset($this->actions[$action])) {
            $verbs = $this->actions[$action];
        } elseif (isset($this->actions['*'])) {
            $verbs = $this->actions['*'];
        } else {
            return $event->isValid;
        }
        $verb = Yii::$app->getRequest()->getMethod();

        $allowed = array_map('strtoupper', $verbs);

        if (!in_array($verb, $allowed)) {
            $event->isValid = false;
            Yii::$app->getResponse()->getHeaders()->set('Allow', implode(', ', $allowed));
            throw new MethodNotAllowedHttpException("Method not allowed. This url only handle the following request methods: " . implode(', ', $allowed) . '.');
        }
        return true;
    }

}
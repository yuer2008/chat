<?php
namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\helpers\Json;

class AppController extends Controller
{
    private             $dns = '';

    public function init()
    {
        $this->dns      =   Yii::$app->mongodb2->dsn;   
    }

    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }
}

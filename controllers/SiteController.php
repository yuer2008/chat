<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\models\MberLoginForm;
use app\models\User;
class SiteController extends Controller
{
    public   $layout  = "main";
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
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
    public function init(){
        $view = Yii::$app->view;
        $uid = !empty(Yii::$app->user->getIdentity())? Yii::$app->user->getIdentity()->id:'';
        $view->params['uid'] = $uid;
        if($uid){
            $view->params['username'] = Yii::$app->user->getIdentity()->username;    
        }
    }
    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionLogin()
    {
        $this->layout = 'login';
        if(Yii::$app->request->isAjax){
            $model = new MberLoginForm();
            if($model->load(Yii::$app->request->post()) && $model->login()){
                return json_encode(['s'=>1, 'm'=>'logon success']);
            }else{
                return json_encode(['s'=>0, 'm'=>'logon fail']);
            }
            
        }
        return $this->render('login');
    }
    public function actionReg()
    {
	$this->layout = 'login';
	$model = new User();
        return $this->render('reg', ['model'=> $model]);
    }
    public function actionLogout()
    {
        Yii::$app->user->logout();
        return $this->goHome();
    }


    public function actionChecklogin(){
        if(Yii::$app->user->isGuest){
            echo json_encode(['s'=>0]);
        }else{
            echo json_encode(['s'=>1]);
        }
    }
}

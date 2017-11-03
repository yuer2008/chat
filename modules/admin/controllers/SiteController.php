<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\Userinfo;
use app\models\Effectype;
use app\models\Effectinfo;
use app\models\AskbarM;
use app\models\Effectdownrecs;
use app\models\Userloginlog;
use app\models\Answer;
use yii\mongodb\Query;

class SiteController extends Controller
{
    //public $layout = null;
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'except' => ['logout','login'],
                'rules' => [
                    [
                        //'actions' => ['logout'],
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

    public function actionIndex()
    {

        $jinri_begin    =   date("Y-m-d",time());
        $jinri_end      =   date("Y-m-d 23:59:59",time());

        //会员总数
        $huiyuan_totals =   Userinfo::find()->count();
        $huiyuan_jinri  =   Userinfo::find()->where(["between","u_jointime",$jinri_begin,$jinri_end])->count();

        //音效数量
        $yinxiao_totals =   Effectinfo::find()->count();
        $yinxiao_jinri  =   Effectinfo::find()->where(["between","ei_up_time",strtotime($jinri_begin),strtotime($jinri_end)])->count();

        //问吧数量
        $wbar_totals    =   AskbarM::find()->where(["is_delete"=>0])->count();
        $wbar_jinri     =   AskbarM::find()->where(["between","add_time",strtotime($jinri_begin),strtotime($jinri_end)])->andWhere(["is_delete"=>0])->count();

        //音效下载数量
        $down_totals    =   Effectdownrecs::find()->count();
        $down_jinri     =   Effectdownrecs::find()->where(["between","d_time",strtotime($jinri_begin),strtotime($jinri_end)])->count();




        $this->layout = 'empty';
        return $this->render('index',[
            "huiyuan_totals"=>$huiyuan_totals,
            "huiyuan_jinri"=>$huiyuan_jinri,
            "yinxiao_totals"=>$yinxiao_totals,
            "yinxiao_jinri"=>$yinxiao_jinri,
            "wbar_totals"=>$wbar_totals,
            "wbar_jinri"=>$wbar_jinri,
            "down_totals"=>$down_totals,
            "down_jinri"=>$down_jinri,
        ]);
    }

    public function actionAjaxhuiyuan(){
        Yii::$app->response->format=\yii\web\Response::FORMAT_JSON;
        //每天新注册的会员
        $time       =       time();
        $days       =       date("t",$time);
        $month      =       date("m",$time);

        //循环遍历每一天
        $register=array("name"=>"当日注册会员");
        $huoyue =   array("name"=>"活跃会员");
        for($ii=1;$ii<=$days;$ii++){
            $register["data"][]  =   Userinfo::find()->where(["between","u_jointime",date("Y-m-$ii 00:00:00"),date("Y-m-$ii 23:59:59")])->count();
            $query                =   new Query();
            $huoyue["data"][]    =  count($query->from("userloginlog")->select(["uid"])->where(["between","logintime",strtotime(date("Y-m-$ii 00:00:00")),strtotime(date("Y-m-$ii 23:59:59"))])->distinct("uid"));
                //Userloginlog::find()->select("u_id")->distinct("u_id")->where(["between","logintime",strtotime(date("Y-m-$ii 00:00:00")),strtotime(date("Y-m-$ii 23:59:59"))])->count();
                //$query->from("userloginlog")->select(["u_id"])->distinct("u_id")->all();
        }
        return [$register,$huoyue];

    }

    public  function actionAjaxyinxiao(){
        Yii::$app->response->format=\yii\web\Response::FORMAT_JSON;
        //每天新增的音效数量
        $time       =       time();
        $days       =       date("t",$time);
        $month      =       date("m",$time);

        $register=array("name"=>"当日新增音效");
        $xiazai =  array("name"=>"当日下载音效");
        for($ii=1;$ii<=$days;$ii++){
            $register["data"][]=Effectinfo::find()->where(["between","ei_up_time",strtotime(date("Y-m-$ii 00:00:00")),strtotime(date("Y-m-$ii 23:59:59"))])->count();
            $xiazai["data"][]=Effectdownrecs::find()->where(["between","d_time",strtotime(date("Y-m-$ii 00:00:00")),strtotime(date("Y-m-$ii 23:59:59"))])->count();
        }
        return [$register,$xiazai];
}

    public function actionAjaxwbar(){
        Yii::$app->response->format=\yii\web\Response::FORMAT_JSON;
        //每天新增的音效数量
        $time       =       time();
        $days       =       date("t",$time);
        $month      =       date("m",$time);

        $wbar=array("name"=>"当日新增问吧数量");
        $huida =  array("name"=>"当日回答数");
        for($ii=1;$ii<=$days;$ii++){
            $wbar["data"][]=AskbarM::find()->where(["between","add_time",strtotime(date("Y-m-$ii 00:00:00")),strtotime(date("Y-m-$ii 23:59:59"))])->andWhere(["is_delete"=>0])->count();
            $huida["data"][]=Answer::find()->where(["between","add_time",strtotime(date("Y-m-$ii 00:00:00")),strtotime(date("Y-m-$ii 23:59:59"))])->andWhere(["is_delete"=>0])->count();
        }
        return [$wbar,$huida];
    }


    public function actionLogin()
    {
    	
    	$this->layout = 'empty';
        $model = new \app\models\LoginForm();
        if(Yii::$app->request->post()){
            $model->load(Yii::$app->request->post());
            if(Yii::$app->request->post("remember")!=="on"){
                $model->rememberMe=false;
            }
            //var_dump($model->getUser());
            if($model->validate()&&$model->login()){
                //var_dump(Yii::$app->user);
                $this->redirect(\yii\helpers\Url::to(["site/index"]));
            }else{
            	   // echo 'fail';
            }
        }
        return $this->render("login",["model"=>$model]);
/*        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        } else {
            return $this->render('login', [
                'model' => $model,
            ]);
        }*/
    }

    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        } else {
            return $this->render('contact', [
                'model' => $model,
            ]);
        }
    }

    public function actionAbout()
    {
        $this->layout = 'admin';
        return $this->render('about');
    }
}

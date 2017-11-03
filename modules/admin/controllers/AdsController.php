<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
//use app\models\LoginForm;
//use app\models\ContactForm;
use \app\models\AdsPosition;
use \app\models\AdsList;
use \yii\helpers\Url;

class AdsController extends Controller
{
    //public $layout = null;$this->enableCsrfValidation=false;
    public $enableCsrfValidation=false;
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
               // 'only' => ['logout'],
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
        $this->layout=false;
        $model  =   new AdsPosition();
        $info   =   "";
        //是否是修改页面
        if(Yii::$app->request->get("id")){
            $id     =   (int)Yii::$app->request->get("id");
            $model  =   AdsPosition::find()->where(["ikey"=>$id])->one();
        }
        if(Yii::$app->request->post()){
            /*var_dump(Yii::$app->request->post());
            $model->load(Yii::$app->request->post());
            var_dump($model->attributes);die;*/
            if($model->load(Yii::$app->request->post())){
                $model->adp_user    =   "admin";
                if($model->save()){
                    $list_url   =Url::to(["ads/alllocal"]);
                    if($model->isNewRecord){
                        Yii::$app->session->setFlash("info","<code>添加一条广告位</code><a href=\"$list_url\">查看列表</a>");
                    }else{
                        Yii::$app->session->setFlash("info","<code>修改成功</code><a href=\"$list_url\">查看列表</a>");
                    }

                    return $this->refresh();
                }else{
                    $model->load(Yii::$app->request->post());
                    $info   =   "保存失败";
                }

            }
        }

        return $this->render("index",["model"=>$model,"info"=>$info]);
    }


    public function actionAlllocal(){
        $this->layout="empty";
        $model  =   AdsPosition::find()->orderBy("adp_time desc")->asArray()->all();
        return $this->render("alllocal",["model"=>$model]);
    }


    public function actionEdit(){
        $name       =       Yii::$app->request->post("name");
        $value      =       Yii::$app->request->post("value");
        $ikey       =       Yii::$app->request->post("pk");

        $result     =       AdsPosition::updateAll([$name=>$value],["ikey"=>(int)$ikey]);

        echo json_encode(["result"=>intval($result)]);
    }

    public function actionAddads(){
        $this->layout="empty";
        $model  =   new AdsList();
        $model->scenario="add";
        $info   =   "";
        if(Yii::$app->request->get("id")){
            $id     =   (int)Yii::$app->request->get("id");
            $model  =   AdsList::find()->where(["ikey"=>$id])->one();
            $model->ads_begin   =   date("m/d/y h:i A",$model->ads_begin);
            $model->ads_end   =   date("m/d/y h:i A",$model->ads_end);
            $model->scenario=   "update";
        }
        if(Yii::$app->request->post()){
            if($model->load(Yii::$app->request->post())){
                $file=$model->ads_image   =   \yii\web\UploadedFile::getInstance($model,"ads_image");
                if($model->validate()){
                    $model->ads_user    =   "admin";
                    $model->ads_begin   =   strtotime($model->ads_begin);
                    $model->ads_end     =   strtotime($model->ads_end);
                    //$file = $model->ads_image   =   \yii\web\UploadedFile::getInstance($model,"ads_image");
                    if($model->ads_image){
                        $savepath   =   time().".".$file->extension;
                        $model->ads_image   =   $savepath;
                        $model->ads_path    =   $savepath;
                    }
                    $model->ads_user    =   "admin";
                    if($model->save()){
                        if($model->ads_image){
                            $file->saveAs(Yii::getAlias("@webroot")."/upload/image/".$savepath);
                        }
                        $list_url   =Url::to(["ads/allads"]);
                        if($model->isNewRecord){
                            Yii::$app->session->setFlash("info","<code>已添加一条广告</code><a href=\"$list_url\">查看列表</a>");
                        }else{
                            Yii::$app->session->setFlash("info","<code>修改一条广告</code><a href=\"$list_url\">查看列表</a>");
                        }
                        return $this->refresh();
                    }else{
                        $model->load(Yii::$app->request->post());
                        $model->ads_image   =   \yii\web\UploadedFile::getInstance($model,"ads_image");
                        $info   =   "保存失败";
                    }
                }else{
                    $model->load(Yii::$app->request->post());
                    $model->ads_image   =   \yii\web\UploadedFile::getInstance($model,"ads_image");
                    $info   =   "保存失败";
                }
            }
        }
        return $this->render("addads",["model"=>$model,"info"=>$info]);
    }

    public function actionAllads(){
        $lists  =   AdsList::find()->where(["ads_status"=>1])->asArray()->all();
        return $this->render("allads",["lists"=>$lists]);
    }


    public function actionTest(){
        $this->layout="empty";
        $model  =   new AdsPosition();
        $info   =   "";
        if(Yii::$app->request->post()){
            /*var_dump(Yii::$app->request->post());
            $model->load(Yii::$app->request->post());
            var_dump($model->attributes);die;*/
            if($model->load(Yii::$app->request->post())){
                $model->adp_user    =   "admin";
                if($model->save()){
                    $list_url   =Url::to(["ads/alllocal"]);
                    Yii::$app->session->setFlash("info","<button>已添加一条广告位</button><a href=\"$list_url\">查看列表</a>");
                    return $this->refresh();
                }else{
                    $model->load(Yii::$app->request->post());
                    $info   =   "保存失败";
                }

            }
        }
        return $this->render("test",["model"=>$model,"info"=>$info]);
    }


}

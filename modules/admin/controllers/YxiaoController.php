<?php

namespace app\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\helpers\Json;
use app\models\EffecterSqinmgo;
use app\models\Effectype;
use yii\data\Pagination;
use yii\helpers\Url;
use yii\mongodb\Query;

class YxiaoController extends Controller
{
    public   $layout    =   "yxiao";
    private  $dns       =   '';  
    private  $dns2       =   '';  
    public $enableCsrfValidation = false;
    
    public function init()
    {
        $this->dns           =   Yii::$app->mongodb->dsn;   
        $this->dns2          =   Yii::$app->mongodb2->dsn;   
    }

    public function behaviors()
    {
        return [
            
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

    /*
    * 功能：默认action
    */
    public function actionIndex()
    {
        echo "^_^";
    }
    
    /*
    * 功能：24Hour's音效全部列表
    */
    public function actionFor24h()
    {
        $d_00           =   strtotime(date('Y-m-d',time()));
        $d_24           =   $d_00 + 24*60*60 - 1;          
        $condArr        =   [ 'ei_up_time'   => ['$lte' => $d_24, '$gte' => $d_00 ] ];

        $mongoClient    =   new \MongoClient($this->dns);
        $collection     =   $mongoClient->xoxdb->effectinfo;    
        $sortArr        =   [ 'ei_up_time' => -1 ];
        $cursorCur      =   $collection->find($condArr)->sort($sortArr);
        $arrayCur       =   [];
        foreach ($cursorCur as $value) {
            $arrayCur[] =   $value;
        }

        $this->layout   =   "empty";
        return $this->render('for24h',[
            'data'  =>  $arrayCur,
        ]);
    }

    /*
    * 功能：音效全部列表
    */
    public function actionAlllist()
    {
        $mongoClient    =   new \MongoClient($this->dns);
        $collection     =   $mongoClient->xoxdb->effectinfo;    
        $sortArr        =   [ 'ei_up_time' => -1 ];
        $cursorCur      =   $collection->find()->sort($sortArr);
        $arrayCur       =   [];
        foreach ($cursorCur as $value) {
            $arrayCur[] =   $value;
        }

        $this->layout   =   "empty";
        return $this->render('alllist',[
            'data'  =>  $arrayCur,
        ]);
    }    

    /*
    * 功能：音效师申请列表
    */
    public function actionShenqin()
    {
        $mongoClient    =   new \MongoClient($this->dns);
        $collection     =   $mongoClient->xoxdb->effecter_sqin;    
        $sortArr        =   [ 'er_jointime' => -1 ];
        $cursorCur      =   $collection->find()->sort($sortArr);
        $arrayCur       =   [];
        foreach ($cursorCur as $value) {
            $arrayCur[] =   $value;
        }

        $this->layout   =   "empty";
        return $this->render('shenqin',[
            'data'  =>  $arrayCur,
        ]);
    }

    /*
    * 功能：音效师列表
    */
    public function actionYxiaoer()
    {
        $mongoClient    =   new \MongoClient($this->dns);
        $collection     =   $mongoClient->xoxdb->effecter;    
        $sortArr        =   [ 'er_jointime' => -1 ];
        $cursorCur      =   $collection->find()->sort($sortArr);
        $arrayCur       =   [];
        foreach ($cursorCur as $value) {
            $arrayCur[] =   $value;
        }

        $this->layout   =   "empty";
        return $this->render('yxiaoer',[
            'data'  =>  $arrayCur,
        ]);
    }     

    /*
    * 功能：音效评论列表
    */
    public function actionPinlun()
    {
        $mongoClient    =   new \MongoClient($this->dns);
        $collection     =   $mongoClient->xoxdb->effect_pinlun;    
        $sortArr        =   [ 'time' => -1 ];
        $cursorCur      =   $collection->find()->sort($sortArr);
        $arrayCur       =   [];
        foreach ($cursorCur as $value) {
            $arrayCur[] =   $value;
        }

        $this->layout   =   "empty";
        return $this->render('pinlun',[
            'data'  =>  $arrayCur,
        ]);
    }        

    /*
     *  功　　能：不予能为音效师并删除音效师申请(ajax调用)
     *  编 码 人：wone
     *  编码时间：2015/07/14
    */
    public function actionScyxersqin()
    {
        if (Yii::$app->request->isAjax){
            $tip            =   "must_login";
            // $uid            =   @Yii::$app->user->getIdentity()->uid;
            $uid            =   10221;
            if ($uid){
                $wid            =   (int)Yii::$app->request->post('wid');
                if ($wid){
                    $condition  =   [ 'er_uid' => (int)$wid ];
                    $integer    =   EffecterSqinmgo::deleteAll($condition);
                    if ($integer){
                        $tip =  "success";
                    }else{
                        $tip =  "op_fail";
                    }                
                }
            } 
        }else{
            $tip = "source_is_error";
        }
        return JSON::encode(array('tip'=>$tip));
    }

    /*
     *  功　　能：准予成为音效师(ajax调用)
     *  编 码 人：wone
     *  编码时间：2015/07/20
    */
    public function actionOkyxersqin()
    {
        if (Yii::$app->request->isAjax){
            $tip            =   "must_login";
            // $uid            =   @Yii::$app->user->getIdentity()->uid;
            $uid            =   10221;
            if ($uid){
                $wid            =   (int)Yii::$app->request->post('wid');
                if ($wid){
                    $condition  =   [ 'er_uid' => (int)$wid ];
                    $integer    =   EffecterSqinmgo::deleteAll($condition);
                    if ($integer){
                        
                        $mongoClient    =   new \MongoClient($this->dns);
                        $collection     =   $mongoClient->xoxdb->effecter;
                        $conditionArr0  =   [ 'er_uid' => (int)$wid ];
                        $recCount       =   $collection->count($conditionArr0);
                        if ($recCount == 0){
                            $newArr                 =    [];
                            $newArr['er_uid']       =    (int)$wid;
                            $newArr['er_grade']     =    1;
                            $newArr['er_status']    =    1;
                            $newArr['er_tuijian']   =    0;
                            $newArr['er_jointime']  =    time();
                            $result                 =    $collection->insert($newArr);
                            if ($result){
                                $tip =  "success";
                            }else{
                                $tip =  "op_fail";
                            }
                        }else{
                            $tip =  "have_is_yxiaoer";
                        }
                    }else{
                        $tip =  "op_fail";
                    }                
                }
            } 
        }else{
            $tip = "source_is_error";
        }
        return JSON::encode(array('tip'=>$tip));
    } 

    /*  
    *   该方法功能：测试本机连接MongoDB是否正常的程序
    */
    public function actionTest_mongodb_status()
    {
        try{
            echo "测试结果如下：<br/><br/>";
            $commongodb         =   @Yii::$app->mongodb2->getCollection('xoxdb');
            if ($commongodb){
                echo "1、公共MongoDB连接测试成功。<br/>";
            }

            $localmongodb       =   @Yii::$app->mongodb->getCollection('xoxdb');
            if ($localmongodb){
                echo "2、本机MongoDB连接测试成功。";
            }  
        } catch (Exception $e) {   
            print $e->getMessage();
        }
    }
    /**  
    *  音效分类列表 
    */
    public function actionCategory(){

        $this->layout   =   "empty";
        $data = Effectype::getList([]);
        $pages = new Pagination(['totalCount' =>$data->count(), 'pageSize' => '10']);
        $model = $data->offset($pages->offset)->limit($pages->limit)->all();

        return $this->render('category_index',
            [
            'model' => $model,
            'pages' => $pages,
            'keyword' => @$keyword,
            ]);
    }

    /**  
    *  音效分类修改
    */
    public function actionCategoryedit(){
        if(Yii::$app->request->isAjax){
            $name = Yii::$app->request->post('name');
            $value = Yii::$app->request->post('value');
            $ikey = Yii::$app->request->post('pk');
            if(empty($ikey) || empty($name) || empty($value)){
                echo json_encode(['s'=>0,'m'=>'params error']);die;
            }
            $e =  Effectype::findOne($ikey);
            $e->$name = $value;
            $rs = $e->update();
            if($rs === false){
              echo json_encode(['s'=>0,'m'=>'fail']);  
            }else{
                echo json_encode(['s'=>1,'m'=>'success']);
            }
        }
    }
    /**  
    *  音效分类删除
    */
    public function actionCategorydel(){
        if(Yii::$app->request->isAjax){
            $ikey = Yii::$app->request->post('pk');
            if(empty($ikey)){
                echo json_encode(['s'=>0,'m'=>'params error']);die;
            }
            $rs =  Effectype::findOne($ikey)->delete();
            if($rs === false){
              echo json_encode(['s'=>0,'m'=>'fail']);  
            }else{
                echo json_encode(['s'=>1,'m'=>'success']);
            }
        }
    }

    /**  
    *  音效分类增加
    */
    public function actionCategoryadd(){
        
        $name = Yii::$app->request->post('name');
        if(empty($name)){
            echo '不能为空';die;
        }
        $query = new Query;
        $max = $query->from('effectype')->max('eid');
        $m = new  Effectype();
        $m->etype = $name;
        $m->eorder = 0 ;
        $m->eid = $max+1;
        $rs = $m->save();
        if($rs === false){
          echo '新增失败';  
        }else{
            $this->redirect(Url::toRoute(['category']));
        }
        
    }
    
    /*
    * 功能：测试代码
    */
    public function actionTmp()
    {
        echo time();
    }

    
}

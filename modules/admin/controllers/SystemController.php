<?php

namespace app\modules\admin\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;

use yii\data\Pagination;
use app\modules\admin\models\Config;
use yii\helpers\Url;
use app\modules\admin\models\Keyword;

class SystemController extends Controller
{
    public function init(){
        parent::init();
        $this->layout='admin';
    }
    //public $layout = null;
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
    // 过滤词列表
    public function actionKeysku()
    {
        $m = new Keyword();
        $page = Yii::$app->request->get('page');
        $keyword = Yii::$app->request->get('keyword');
        if(empty($keyword) && Yii::$app->request->post('keyword')){
            $keyword = Yii::$app->request->post('keyword');
        }
        

        $data = $m->getAllList(['keyword'=>$keyword]);
        
        $pages = new Pagination(['totalCount' =>$data->count(),'page'=>$page-1 , 'pageSize' => '10','params'=>['keyword'=>$keyword]]);
        $model = $data->offset($pages->offset)->limit($pages->limit)->all();
        return $this->render("keysku",[
            'model'=>$model,
            'pages' => $pages,
            'keyword' => $keyword,
            ]);
    }
    // key word edit
    public function actionEdit(){
        $keyword = Yii::$app->request->post('value');
        $id = Yii::$app->request->post('pk');
        $customer = Keyword::findOne($id);
        $customer->word_name = $keyword;
        
        $rs = $customer->update(false);
        if($rs !== false){
            return json_encode(['err_code'=>1]);    
        }else{
            return json_encode(['err_code'=>0]);    
        }
        
    }
    // 过滤词添加
    public function actionKeyadd(){
        $model = new Keyword();
        if(!empty(Yii::$app->request->post())){
            $word = Yii::$app->request->post('keyword');
            $model->word_name = $word;
            $rs = $model->insert();

        }
        return $this->render("keyadd",[
            'model'=>$model,
            ]);
    }
    // 过滤词删除
    public function actionKeydel(){
        $model = new Keyword();
        $id = Yii::$app->request->get('id');
        if(Yii::$app->request->isAjax){
            if(empty($id)){
                echo json_encode(['s'=>0,'m'=>'key is empty']);
            }
            // $model->findOne($id);
            $rs =Keyword::getCollection()->remove(['_id'=>$id]);
            // $rs = $model->delete();
            if($rs === false){
                echo json_encode(['s'=>0, 'm'=>'fail']);
            }else{
                echo json_encode(['s'=>1, 'm'=>'success']);
            }
        }
    }

    //全局设置
    public function actionQuanju(){
        $postdata = Yii::$app->request->post();
        $model = Config::getInfo('global');
        $config_info=[];
        if(!empty($model)){
            $config_info = json_decode($model['value'],true);
        }
        
        if(!empty($postdata)){

            $data = [
                'website_name'=>$postdata['website_name'],
                'website_version'=>$postdata['website_version'],
                'icp'=>$postdata['icp'],
                'com_tel'=>$postdata['com_tel'],
                'com_fax'=>$postdata['com_fax'],
                'com_email'=>$postdata['com_email'],
                'com_address'=>$postdata['com_address'],
                'zipcode'=>$postdata['zipcode'],

            ];
            if(empty($postdata['_id'])){
                //insert
                $config = new Config;
                $config->key = 'global';
                $config->value = json_encode($data);
                $config->update_time = time();
                $rs = $config->insert();
            }else{
                $rs = Config::edit('global',json_encode($data));    
            }
            $this->redirect(Url::toRoute(['quanju']));
            
        }
        
        return $this->render('quanju',[
                '_id' =>$model['_id'],
                'model' =>$config_info,
                'tabs'=>'quanju'
            ]);
    }

    //上传设置
    public function actionUpsets(){
        $postdata = Yii::$app->request->post();
        $model = Config::getInfo('upsets');
        $config_info=[];
        if(!empty($model)){
            $config_info = json_decode($model['value'],true);
        }
        
        if(!empty($postdata)){

            $data = [
                'upload_types'=>$postdata['upload_types'],
                'upload_max_size'=>$postdata['upload_max_size'],
                'mark_img_pos'=>$postdata['mark_img_pos'],
                'is_mark'=>$postdata['is_mark'],
                'real_del'=>$postdata['real_del'],

            ];
            if(empty($postdata['_id'])){
                //insert
                $config = new Config;
                $config->key = 'upsets';
                $config->value = json_encode($data);
                $config->update_time = time();
                $rs = $config->insert();
            }else{
                $rs = Config::edit('upsets',json_encode($data));    
            }
            $this->redirect(Url::toRoute(['upsets']));
            
        }
        
        return $this->render('quanju',[
                '_id' =>$model['_id'],
                'model' =>$config_info,
                'tabs'=>'upsets'
            ]);
    }

}

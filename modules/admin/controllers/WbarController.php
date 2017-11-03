<?php

namespace app\modules\admin\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\AskbarM;
use yii\data\Pagination;
use yii\helpers\Url;

use app\modules\admin\models\AskbarType;


class WbarController extends Controller
{
    public function init(){

        $this->layout = 'admin';
    }
    //public $layout = null;
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
    //问题列表
    public function actionIndex(){
        // phpinfo();die;
        $m = new AskbarM();
        // 24小时内的
        $limit = Yii::$app->request->get('limit');
        if(empty($limit) && !empty(Yii::$app->request->post('limit'))){
            $limit = Yii::$app->request->post('limit');
        }
        
        $keyword = Yii::$app->request->post('keyword');

        $data = $m->getAskList(['keyword'=>$keyword,'limit'=>$limit]);
        
        $pages = new Pagination(['totalCount' =>$data->count(), 'pageSize' => '10']);
        $model = $data->offset($pages->offset)->limit($pages->limit)->all();

        
        $view = Yii::$app->view;
        $view->params['limit']=$limit;
        return $this->render('index',[
            
            'model' => $model,
            'pages' => $pages,
            'keyword' => $keyword,
            'limit' => $limit,
            ]);
        
    }
    public function actionFor24h(){

         $this->redirect(Url::toRoute(['index', 'limit' => 24]));

    }
     // 删除问题
    public function actionDelete(){
        $id = Yii::$app->request->get('id');
        $rs = AskbarM::remove($id);
        if($rs !== false){
            echo json_encode(['s'=>1,'m'=>'删除成功']);
        }else{
            echo json_encode(['s'=>0,'m'=>'删除失败']);
        }
    }

    //分类列表
    public function actionCategoryindex()
    {
        $m = new AskbarType();
        $list = $m->getMenuList();
        // print_r($list);
        return $this->render('category_index',['category_list'=>$list]);
    }
    // 分类列表
    public function actionAllcategory(){
        $pid = Yii::$app->request->post('ikey',0);
        $m = new AskbarType();
        $data = $m->getTypesByPid($pid);
        echo json_encode($data);
    }

    //新增分类
    public function actionAddcat(){
        $pt_name = Yii::$app->request->post('pt_name');
        $pid = Yii::$app->request->post('pid',0);
        if(Yii::$app->request->isAjax){
            if(empty($pt_name)){
                echo json_encode(['s'=>0,'m'=>'不能为空']);die;
            }
            $m =  new AskbarType();
            $m->pt_name = $pt_name;
            $m->pt_parent_ikey = $pid;
            $m->pt_order = 1;
            $rs = $m->save();
            if($rs !== false){
                $newid = AskbarType::getDb()->getLastInsertID();
                echo json_encode(['s'=>1,'m'=>'新增成功','d'=>$newid]);
            }else{
                echo json_encode(['s'=>0,'m'=>'新增失败']);
            }
        }else{
            if(empty($pt_name)){
                echo '分类名不能为空';die;
            }
            $m =  new AskbarType();
            $m->pt_name = $pt_name;
            $m->pt_parent_ikey = $pid;
            
            $rs = $m->save();
            if($rs !== false){
                // $newid = AskbarType::getDb()->getLastInsertID();
                $this->redirect(Url::toRoute(['categoryindex']));
            }else{
                echo '新增失败';
            }
        }
        
    }

    //编辑分类
    public function actionEditcat(){
        $pid = Yii::$app->request->post('ikey',0);
        $new_name = Yii::$app->request->post('pt_name');

        if(empty($new_name)){
            echo json_encode(['s'=>0,'m'=>'不能为空']);die;
        }
        $m =  AskbarType::findOne($pid);
        $m->pt_name = $new_name;
        $rs = $m->update();
        if($rs !== false){
            echo json_encode(['s'=>1,'m'=>'修改成功']);
        }else{
            echo json_encode(['s'=>0,'m'=>'修改失败']);
        }
    }
    //编辑分类
    public function actionEditcatorder(){
        $id = Yii::$app->request->post('id',0);
        $target_id = Yii::$app->request->post('targetId');
        $move_type = Yii::$app->request->post('moveType');

        if(empty($id) || empty($target_id) || empty($move_type)){
            echo json_encode(['s'=>0,'m'=>'参数错误']);die;
        }
        if($move_type == 'inner'){
            echo json_encode(['s'=>0,'m'=>'只能同级排序']);die;   
        }
        $m2 =  AskbarType::findOne($id);
        $m1 =  AskbarType::findOne($target_id);
        
        $order = !empty($m1->pt_order)?$m1->pt_order:1;
        if($move_type == 'next'){
            $m2->pt_order = $order+1;
        }else{
            $m2->pt_order = $order-1;
        }
        
        $rs = $m2->update();
        if($rs !== false){
            echo json_encode(['s'=>1,'m'=>'修改成功']);
        }else{
            echo json_encode(['s'=>0,'m'=>'修改失败']);
        }
    }

    // 删除分类
    public function actionDeletecat(){
        $pid = Yii::$app->request->post('ikey',0);
        
        $rs = AskbarType::findOne($pid)->delete();
        if($rs !== false){
            echo json_encode(['s'=>1,'m'=>'删除成功']);
        }else{
            echo json_encode(['s'=>0,'m'=>'删除失败']);
        }
    }

   
    
}

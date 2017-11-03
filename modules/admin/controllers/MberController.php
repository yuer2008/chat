<?php

namespace app\modules\admin\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\mongodb\Query;
use yii\web\Controller;
use yii\filters\VerbFilter;

use app\models\Userinfo;
use app\models\UserinfoHadrwares;

use yii\helpers\Json;



class MberController extends Controller
{
    public   $layout    =   "";
    private  $dns       =   '';  
    private  $dns2      =   '';  
    
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
    * 功能：72Hour's内注册的全部会员列表
    */
    public function actionFor72h()
    {
        $d_00           =   strtotime(date('Y-m-d',time()));
        $d_72           =   $d_00 - 24*3*60*60 - 1;          
        $condArr        =   [ ">", "u_jointime", date("Y-m-d H:i:s", $d_72) ];
        $arrayCur       =   Userinfo::find()->where($condArr)->orderBy('u_jointime desc')->all();

        $this->layout   =   "empty";
        return $this->render('for72h',[
            'data'  =>  $arrayCur,
        ]);
    }

    /*
    * 功能：超3个月未登录的全部会员列表
    */
    public function actionCao3ms()
    {
        $d_00           =   strtotime(date('Y-m-d',time()));
        $d_24           =   $d_00 - 24*90*60*60 - 1;          
        $condArr        =   [ 'logintime'   => ['$lte' => $d_00, '$gte' => $d_24 ] ];

        $mongoClient    =   new \MongoClient($this->dns);
        $collection     =   $mongoClient->xoxdb->userloginlog;   
        $fieldsArr      =   [ '_id'=>0, 'uid'=>1 ]; 
        $sortArr        =   [ 'logintime' => -1 ];
        $cursorCur      =   $collection->find($condArr)->fields($fieldsArr)->sort($sortArr);
        $arrayCur       =   [];
        foreach ($cursorCur as $value) {
            $arrayCur[] =   $value;
        }

        $llArr          =   [];
        foreach ($arrayCur as $value) {
            $news   =   $value['uid'];
            if (!in_array($news,$llArr)){
                array_push($llArr, $news);
            }
        } 

        $condArr        =   [ "NOT IN", "u_id", $llArr ];
        $arrayCur       =   Userinfo::find()->where($condArr)->orderBy('u_jointime desc')->all();            

        $this->layout   =   "empty";
        return $this->render('cao3ms',[
            'data'  =>  $arrayCur,
        ]);
    }    

    /*
    * 功能：会员全部列表
    */
    public function actionAlllist()
    {
        $arrayCur       =   Userinfo::find()->orderBy('u_jointime desc')->all();
        $this->layout   =   "empty";
        return $this->render('alllist',[
            'data'  =>  $arrayCur,
        ]);
    } 

    /*
     *  功　　能：删除会员(ajax调用)
     *  编 码 人：wone
     *  编码时间：2015/07/16
    */
    public function actionScmber()
    {
        $tip            =   "must_login";
        if (Yii::$app->request->isAjax){
            $uid            =   (int)Yii::$app->request->post('uid');
            if ($uid)
            {
                /**begin******* 这4行是删除Mysql->userinfo 和 UserinfoHadrwares 信息 ************/
                $conArr1    =   [ 'u_id' => (int)$uid ];
                $integer1   =   @Userinfo::deleteAll($conArr1);

                $conArr2    =   [ 'uh_uikey' => (int)$uid ];
                $integer2   =   @UserinfoHadrwares::deleteAll($conArr2);
                /**end***************************************************************************/

                if ($integer1)
                {
                    $mongoClient    =   new \MongoClient($this->dns2);
                    /**begin******* 这几行是从公共Mongodb中的userinfo中删除信息与userinfoaccount更新信息的*******/
                    $collection     =   $mongoClient->xoxdb->userinfo;   
                    $conArr3        =   [ 'u_id' => (int)$uid ];
                    $sl             =   @$collection->remove($conArr3, ["justOne" => true]);
        
                    $collection     =   $mongoClient->xoxdb->userinfoaccount;   
                    $conArr4        =   [ 'xn_uid' => (int)$uid ];
                    $newdata        =   [ 'xn_enable' => 1 ];
                    $sl             =   @$collection->update($conArr4,['$set'=>$newdata]);          
                    /**end***************************************************************************************/

                    $tip =  "success";
                }else{
                    $tip =  "op_fail";
                }             
            }            
        }else{
            $tip        =   "method_error";
        }
        return JSON::encode(array('tip'=>$tip));
    }     

    /*
    * 功能：全部会员登录痕迹列表
    */
    public function actionLoginset()
    {
        $mongoClient    =   new \MongoClient($this->dns);
        $collection     =   $mongoClient->xoxdb->userloginlog;   
        $sortArr        =   [ 'logintime' => -1 ];
        $cursorCur      =   $collection->find()->sort($sortArr);
        $arrayCur       =   [];
        foreach ($cursorCur as $value) {
            $arrayCur[] =   $value;
        }

        $this->layout   =   "empty";
        return $this->render('loginset',[
            'data'  =>  $arrayCur,
        ]);
    } 

    /*
    * 功能：哼哼唧唧待选帐号
    */
    public function actionDxuanhao()
    {
        $sulian         =   800;
        $mongoClient    =   new \MongoClient($this->dns2);
        $collection     =   $mongoClient->xoxdb->userinfoaccount;   
        $fieldsArr      =   [ '_id'=>0 ];
        $condArr        =   [ 'xn_enable'=>1 ];
        $sortArr        =   [ '_id' => 1 ];
        $cursorCur      =   $collection->find($condArr)->fields($fieldsArr)->sort($sortArr)->limit($sulian);
        $arrayCur       =   [];
        foreach ($cursorCur as $value) {
            $arrayCur[] =   $value;
        }

        $this->layout   =   "empty";
        return $this->render('hhjjdxuanhao',[
            'data'  =>  $arrayCur,
        ]);
    }   

    /*
    * 功能：哼哼唧唧靓号库
    */
    public function actionLianhao()
    {
        $sulian         =   800;
        $mongoClient    =   new \MongoClient($this->dns2);
        $collection     =   $mongoClient->xoxdb->userinfoaccount;   
        $fieldsArr      =   [ '_id'=>0 ];
        $condArr        =   [ 'xn_enable'=>2 ];
        $sortArr        =   [ '_id' => 1 ];
        $cursorCur      =   $collection->find($condArr)->fields($fieldsArr)->sort($sortArr)->limit($sulian);
        $arrayCur       =   [];
        foreach ($cursorCur as $value) {
            $arrayCur[] =   $value;
        }

        $this->layout   =   "empty";
        return $this->render('hhjjlianhao',[
            'data'  =>  $arrayCur,
        ]);
    } 

    /*
    * 功能：测试代码
    */
    public function actionTmp()
    {
        echo 10/4;
        echo 10/5;
    }

    
}

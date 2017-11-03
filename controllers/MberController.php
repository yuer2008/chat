<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\mongodb\Query;
use app\models\Userinfo;
use app\models\Provincemongo;
use yii\helpers\Json;
use app\models\Snbindlog;
use app\models\Userloginlog;

class MberController extends Controller
{
    public   $layout  = "mber";
    private  $dns     = '';  
    private  $dns2    = '';  
    
    public function init()
    {
        $this->dns           =   Yii::$app->mongodb2->dsn;   
        $this->dns2          =   Yii::$app->mongodb->dsn;   
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
                "width"=>85,
                'height'=>30,
                'backColor'=>0xEDECEE,
                'minLength'=>5,
                'maxLength'=>5,
                "foreColor"=>0xB8678E
            ],
        ];
    }

    /*  
    *   功能：会员首页
    */
    public function actionIndex()
    {
        $uid                =   @Yii::$app->user->getIdentity()->uid;
        if ($uid){
            $model          =   new Userinfo();
            $uinfo          =   Userinfo::find()->where([ 'u_id' => $uid ])->asArray()->one();

            // $conditionArr   =   [ 'ikey' => $ikey ];
            $mongoClient    =   new \MongoClient($this->dns);
            $collection     =   $mongoClient->xoxdb->province;
            $cursorCur      =   $collection->find()->fields(['_id'=>0]);
            $shenArray      =   array();
            foreach ($cursorCur as $value) {
                $shenArray[] =   $value;
            }
            
            $shenid         =   $uinfo['u_province_id'];
            $shenid         =   $shenid?$shenid:440000;
            $collection     =   $mongoClient->xoxdb->city;
            $cursorCur      =   $collection->find(['provinceID' => (int)$shenid])->fields(['_id'=>0]);
            $cityArray      =   array();
            foreach ($cursorCur as $value) {
                $cityArray[] =   $value;
            }

            $areaid         =   $uinfo['u_city_id'];
            $areaid         =   $areaid?$areaid:440300;
            $collection     =   $mongoClient->xoxdb->county;
            $cursorCur      =   $collection->find(['cityID' => (int)$areaid])->fields(['_id'=>0]);
            $countArray     =   array();
            foreach ($cursorCur as $value) {
                $countArray[] =   $value;
            }

            $model->u_sex               =   $uinfo['u_sex'];
            $model->u_shengxiao         =   $uinfo['u_shengxiao'];
            $model->u_constellation     =   $uinfo['u_constellation'];

            $model->u_bloodtype         =   $uinfo['u_bloodtype'];
            $model->u_profession        =   $uinfo['u_profession'];
            $model->u_education         =   $uinfo['u_education']; 

            $model->u_signature         =   $uinfo['u_signature']; 
            $model->u_intro             =   $uinfo['u_intro'];
            $model->u_xox_account       =   $uinfo['u_xox_account'];

            $model->u_email             =   $uinfo['u_email'];            
            $model->u_mobile            =   $uinfo['u_mobile'];

            return $this->render('index',[
                'model'        =>      $model,
                'uinfo'        =>      $uinfo,

                'mshen'        =>      $shenArray,
                'mcitys'       =>      $cityArray,
                'tareda'       =>      $countArray,
            ]);                 
        }else{
            return $this->redirect("./index.php?r=yinxiao/index");
        }       
    }  

    /*  
    *   功能：修改密码表单
    */
    public function actionModifypass()
    {
        $uid                =   @Yii::$app->user->getIdentity()->uid;
        if ($uid){
            $model          =   new Userinfo();
            $uinfo          =   Userinfo::find()->select(['u_id', 'u_xox_account', 'u_password'])->where([ 'u_id' => $uid ])->asArray()->one();

            return $this->render('modifypass',[
                'model'        =>      $model,
                'uinfo'        =>      $uinfo,
            ]);                 
        }else{
            return $this->redirect("./index.php?r=yinxiao/index");
        }       
    } 

        /*
         *  功　　能：实际修改密码程序(ajax调用)
         *  编 码 人：wone
         *  编码时间：2015/06/29 14:50:40
        */
        public function actionGenxinupass()
        {
            if (Yii::$app->request->isajax){
                $tip                =   "must_login";
                $uid                =   @Yii::$app->user->getIdentity()->uid;
                if ($uid)
                {
                    $initp          =   Yii::$app->request->post('initpass');
                    $newsp          =   Yii::$app->request->post('newspass');
                    $ressp          =   Yii::$app->request->post('resspass');

                    if ($newsp === $ressp)
                    {
                        if (strlen(trim($newsp))<6){
                            $tip = "pass_short";
                        }else{
                            $condA      =   ['u_id' => $uid, 'u_password' => md5($initp)];
                            $datas      =   Userinfo::find()->where($condA)->one();
                            if ($datas)
                            {
                                $nData  =   [];
                                $nData['ikey']          =   $datas['ikey'];
                                $nData['u_id']          =   $datas['u_id'];
                                $nData['u_password']    =   md5($newsp);
                                $datas->attributes      =   $nData;
                                if ($datas->validate()) 
                                {
                                    if ($datas->update() !== false) {
                                        $tip = "update_success";
                                    }else{
                                        $tip = "update_fail";
                                    }
                                }else{
                                    $tip = "vlidate_fail";
                                }                        
                            }else{
                                $tip    =   "passerror";
                            }
                        }
                    }else{
                        $tip    =   "2pnotsame";
                    }
                } 
            }else{
                $tip         =   "source_error";
            }                
            return JSON::encode(array('tip'=>$tip));
        }    

    /*  
    *   功能：关联设备清单
    */
    public function actionMydevicess()
    {
        $uid                =   @Yii::$app->user->getIdentity()->uid;
        if ($uid){
            $hardw          =   \app\models\UserinfoHadrwares::find()->select(['ikey', 'uh_uikey', 'uh_seriesno', 'uh_proname', 'uh_addtime', 'us_isdefault'])->where([ 'uh_uikey' => $uid ])->asArray()->all();
            return $this->render('mydevicess',[
                'hardw'        =>      $hardw,
            ]);                 
        }else{
            return $this->redirect("./index.php?r=yinxiao/index");
        }  
    }

        /*  
        *   功能：删除关联设备
        */
        public function actionDeletedevices()
        {
            if (Yii::$app->request->isajax){
                $tip                =   "must_login";
                $uid                =   @Yii::$app->user->getIdentity()->uid;
                if ($uid){
                    $ikey           =   (int)Yii::$app->request->get('ikey');
                    if ($ikey&&$ikey>0){
                        $hardw      =   \app\models\UserinfoHadrwares::deleteAll([ 'ikey' => $ikey, 'uh_uikey' => $uid ]);
                        if ($hardw){
                            $mongoClient    =   new \MongoClient($this->dns2);
                            $collection     =   $mongoClient->xoxdb->userinfo; 
                            $where          =   ['u_id'=>(int)$uid];
                            $snset          =   getmbersnforuid($uid);
                            $newdata        =   ['u_sn'=>$snset];
                            $result         =   $collection->update($where,['$set'=>$newdata]);                            
                            $tip    =   "op_success";
                        }else{
                            $tip    =   "op_fail";
                        }               
                    }else{
                        $tip         =   "param_error";
                    }
                }                
            }else{
                $tip         =   "source_error";
            }
            return JSON::encode(array('tip'=>$tip));
        }
        /**
        * 给控制面板提供接口，解除sn的绑定
        */
        public function actionUnbindsn(){
            $key_str = 'xoxuser';
            $sn = Yii::$app->request->get('sn');
            $key = Yii::$app->request->get('key');
            $v = Yii::$app->request->get('v');

            if(empty($sn)){
                echo 1001;die;
            }
            if(empty($key)){
                echo 1002;die;   
            }
            if($key != md5($sn.$key_str.$v)){
                echo 1003;die;   
            }
            $info = \app\models\UserinfoHadrwares::findOne(['uh_seriesno' => $sn]);

            if(empty($info)){
                echo 1004;die;
            }
            $uid = $info->uh_uikey;
            
            $hardw      =   \app\models\UserinfoHadrwares::deleteAll([ 'uh_seriesno' => $sn ]);
            if($hardw){
                $mongoClient    =   new \MongoClient($this->dns2);
                $collection     =   $mongoClient->xoxdb->userinfo; 
                $where          =   ['u_id'=>(int)$uid];
                $snset          =   getmbersnforuid($uid);
                $newdata        =   ['u_sn'=>$snset];
                $result         =   $collection->update($where,['$set'=>$newdata]);  
                if($result === false){
                    echo 1007;die;
                }else{
                    echo 0;die;
                }
            }else{ // delete fail
                echo 1006;
            }

        }

        /*  
        *   功能：修改关联设备表单
        */
        public function actionModifydevices()
        {
            $uid                =   @Yii::$app->user->getIdentity()->uid;
            if ($uid){
                
                $ikey           =   (int)Yii::$app->request->get('ikey');
                $hdif           =   \app\models\UserinfoHadrwares::find()->where(['ikey'=>$ikey, 'uh_uikey'=>$uid])->asArray()->one();

                return $this->render('modifydevices',[
                    'hdif'      =>  $hdif,
                ]);                 
            }else{
                return $this->redirect("./index.php?r=yinxiao/index");
            }       
        }

        /*
         *  功　　能：实际修改设备属性程序(ajax调用)
         *  编 码 人：wone
         *  编码时间：2015/06/29 14:50:40
        */
        public function actionGenxinhdif()
        {
            $tip                =   "must_login";
            if (Yii::$app->request->isajax){
                $uid                =   @Yii::$app->user->getIdentity()->uid;
               
                if ($uid)
                {
                    $ikey           =   (int)Yii::$app->request->post('ikey');
                    $snos           =   Yii::$app->request->post('uh_seriesno');
                    $pncs           =   Yii::$app->request->post('uh_proname');
                    if ($ikey&&$ikey>0){// 修改
                        if (strlen(trim($snos))<10){
                            $tip = "sn_short";
                        }else{
                            $condA      =   ['and', "uh_seriesno='$snos'", "ikey<>$ikey"];
                            $exits      =   \app\models\UserinfoHadrwares::find()->where($condA)->exists();
                            if (!$exits){
                                $condB      =   ['ikey' => $ikey];
                                $datas      =   \app\models\UserinfoHadrwares::find()->where($condB)->one();
                                if ($datas)
                                {
                                    $old_sn_no = $datas['uh_seriesno'];
                                    if($old_sn_no != $snos){
                                        if(Snbindlog::getBindTimes($uid) > 0){
                                            return JSON::encode(array('tip'=>'time_limit_24'));
                                        }
                                        if(Snbindlog::getBindTimes($uid, 30) > Snbindlog::MONTH_LIMIT_TIMES){
                                            return JSON::encode(array('tip'=>'time_limit_month'));
                                        }
                                    }

                                    $nData  =   [];
                                    $nData['ikey']          =   $ikey;
                                    $nData['uh_seriesno']   =   $snos;
                                    $nData['uh_proname']    =   $pncs;
                                    $nData['uh_addtime']    =   date("Y-m-d H:i:s");
                                    $datas->attributes      =   $nData;
                                    if ($datas->validate()) 
                                    {
                                        if ($datas->update() !== false) {

                                            $mongoClient    =   new \MongoClient($this->dns2);
                                            $collection     =   $mongoClient->xoxdb->userinfo; 
                                            $where          =   ['u_id'=>(int)$uid];
                                            $snset          =   getmbersnforuid($uid);
                                            $newdata        =   ['u_sn'=>$snset];
                                            $result         =   $collection->update($where,['$set'=>$newdata]);
                                            $tip = "update_success";

                                            //sn change, add bind log
                                            if($old_sn_no != $snos){
                                                $log = new Snbindlog();
                                                $log->seriesno = $snos;
                                                $log->u_id = $uid;
                                                $log->insert();
                                            }
                                            

                                        }else{
                                            $tip = "update_fail";
                                        }
                                    }else{
                                        $tip = "vlidate_fail";
                                    }                        
                                }
                            }else{
                                $tip    =   "exists";
                            }
                        }
                    }else{ // 添加新设备
                        if(Snbindlog::getBindTimes($uid) > 0){
                            return JSON::encode(array('tip'=>'time_limit_24'));
                        }
                        if(Snbindlog::getBindTimes($uid, 30) > Snbindlog::MONTH_LIMIT_TIMES){
                            return JSON::encode(array('tip'=>'time_limit_month'));
                        }
                        
                        if (strlen(trim($snos))<10){
                            $tip = "sn_short";
                        }else{                        
                            $condA      =   ['uh_seriesno'=>"$snos"];
                            // $condA      =   ['and', "uh_seriesno='$snos'", "uh_uikey<>$ikey"];
                            $exits      =   \app\models\UserinfoHadrwares::find()->where($condA)->exists();
                            if (!$exits){
                                $datas  =   new \app\models\UserinfoHadrwares();
                                $nData  =   [];
                                $nData['ikey']          =   $ikey;
                                $nData['uh_uikey']      =   $uid;
                                $nData['uh_seriesno']   =   $snos;
                                $nData['uh_proname']    =   $pncs;
                                $nData['uh_addtime']    =   date("Y-m-d H:i:s");
                                $datas->attributes      =   $nData;
                                if ($datas->validate()) 
                                {
                                    if ($datas->save() !== false) {

                                        $mongoClient    =   new \MongoClient($this->dns2);
                                        $collection     =   $mongoClient->xoxdb->userinfo; 
                                        $where          =   ['u_id'=>(int)$uid];
                                        $snset          =   getmbersnforuid($uid);
                                        $newdata        =   ['u_sn'=>$snset];
                                        $result         =   $collection->update($where,['$set'=>$newdata]);
                                        $tip = "update_success";

                                        //add bind log
                                        $log = new Snbindlog();
                                        $log->seriesno = $snos;
                                        $log->u_id = $uid;
                                        $log->insert();

                                    }else{
                                        $tip = "update_fail";
                                    }
                                }else{
                                    $tip = "vlidate_fail";
                                }                        
                            }else{
                                $tip    =   "exists";
                            }  
                        }                      
                    }

                } 
            }else{
                $tip         =   "source_error";
            }                
            return JSON::encode(array('tip'=>$tip));
        } 


    /*  
    *   功能：登录记录
    */
    public function actionLoginlishi()
    {
        $uid                =   @Yii::$app->user->getIdentity()->uid;
        if ($uid){
            $mongoClient    =   new \MongoClient($this->dns);
            $sortArr        =   [ 'logintime' => -1];
            $conditionArr   =   [ 'uid' => $uid];
            $collection     =   $mongoClient->xoxdb->userloginlog;
            $recCount       =   $collection->count($conditionArr);

            $cup            =   (int)Yii::$app->request->get('p');
            $currpagema     =   $cup&&$cup>0?$cup:1;
            $prepagerec     =   13;
            $limitslian     =   ($currpagema - 1) * $prepagerec;
            $countpages     =   ceil($recCount/$prepagerec); 

            $cursorCur      =   $collection->find($conditionArr)->sort($sortArr)->limit($prepagerec)->skip($limitslian);        
            $arrayCur       =   array();
            foreach ($cursorCur as $value) {
                $loginrec[] =   $value;
            } 
            return $this->render('loginlishi',[
                'loginrec'  =>  $loginrec,
                'countps'   =>  $countpages,
                'efslian'   =>  $recCount,

                'efcurpg'   =>  $currpagema,
                'countps'   =>  $countpages,
                'tadeid'    =>  '',
            ]);                           
        }else{
            return $this->redirect("./index.php?r=yinxiao/index");
        } 
    }

    /*  
    *   功能：信息定制 
    */
    public function actionDingzhiinf()
    {
        return $this->render('dingzhiinf'); 
    }   

    /*  
    *   该方法功能：会员退出
    */
    public function actionLogout()
    {
        $uid    =   (int)Yii::$app->request->get('uid');
        $dsj    =   (int)Yii::$app->request->get('logintime');
        if ($uid<=0||$dsj<=0){
            $session    =   Yii::$app->session;
            $session->open();        
            $uid        =   @Yii::$app->user->getIdentity()->uid;
            $dsj        =   $session->get('logintime');
        }

        if ($uid&&$uid>0){
            @loginlog($uid, $dsj, '', 'logout');
            Yii::$app->user->logout();
        }

        header("Location: ./index.php?r=tool/gaiyao");
    } 

    /*
    *   找回密码请求，显示找回密码表单
    */
    public function actionFindpass()
    {
        $errs           =   [];
        $this->layout   =   'findpass';
        return $this->render('findpass');
    }

    public function actionFindback(){
        $errs   =   [];
        $this->layout   =   'findpass';
        return $this->render('findback');
    }

    /*  
    *   功能：找回密码程序
    *   http://121.201.34.37/index.php?r=mber/findmm&sn=VBBID3ABEGGT&email=465703269@qq.com
    *   http://121.201.34.37/index.php?r=mber/findmm&hhjj=10010316&email=465703269@qq.com
    */
    public function actionFindmm()
    {
        $email      =   Yii::$app->request->get('email');
        $tmp        =   Yii::$app->request->get('account');
        $captcha_code = Yii::$app->request->get('captcha');
        $sn         =   "";
        $hhjj       =   0;
        $captcha = new \yii\captcha\CaptchaValidator();
        $captcha->captchaAction = "mber/captcha";

        if (is_numeric($tmp))
        {
            $hhjj   = (int)$tmp;
        }else{
            $sn     = trim($tmp);
        }
      
        $tip        =   "";
        $isexist    =   0;
        if(!$captcha->createCaptchaAction()->validate($captcha_code,false)){
            $tip = "验证码不正确";
            goto end;
        }
        if ($email&&strlen($email)>6){
            if ($sn&&strlen($sn)>=12)
            {
                $uidArr     =   @\app\models\UserinfoHadrwares::find()->select(['uh_uikey'])->where([ 'uh_seriesno' => $sn ])->asArray()->one(); 
                if ($uidArr)
                {
                    $uid    =   @$uidArr['uh_uikey'];
                    if ($uid)
                    {
                        $isexist   =   @Userinfo::find()->where(['u_id'=>$uid, 'u_email'=>$email])->one(); 
                    }
                }
            }
        }else{
            $tip = "邮箱错误";
            goto end;
        }

        if (!$isexist&&is_numeric($hhjj))
        {
            $isexist   =   @Userinfo::find()->where(['u_xox_account'=>$hhjj, 'u_email'=>$email])->one(); 
        }
        if ($isexist)
        {
            $zhao = $isexist['u_xox_account'];
            if ($zhao)
            {
                //var_dump($isexist);die;
                $authKey        =   $isexist['u_authKey'];
                $accessToken    =   $isexist['u_accessToken'];
                if (@Userinfo::updateAll([ 'u_lock' => 1, 'u_locktime' => time() ], "u_xox_account = $zhao"))
                {
                    try{
                        $url = Yii::$app->urlManager->createAbsoluteUrl(["mber/changemm","email"=>$email,"zhao"=>$zhao,"key"=>$authKey,"token"=>$accessToken]);
                        //$url = "http://121.201.34.37/index.php?r=mber/changemm&email=$email&zhao=$zhao&key=$authKey&token=$accessToken";
                        //echo $url;die;
                        $content  = "<br/> ";            
                        $content .= "<br/> 亲爱的会员 ，您好！";
                        $content .= "<br/> 　　以下是您的密码信息,请查收:";
                        $content .= "<br/> 　　您的会员名(或SN): $sn $hhjj ";
                        $content .= "<br/> <a href='$url' target='_blank'>请点击此链接重置您的密码</a>";
                        $content .= "<br/> ";
                        $content .= "<br/> 如果该链接无效， 请直接复制以下的链接：";
                        $content .= "<br/> <a href='$url' target='_blank'>$url</a>";
            
                        $mail = @Yii::$app->mailer->compose();   
                        @$mail->setTo($email);  
                        @$mail->setSubject("找回密码");  
                        @$mail->setHtmlBody($content);
                        @$mail->send();

                        $tip = "成功";
                        goto end;
                    }catch(Exception $e){
                        $tip = "失败";      goto end;              }                    
                }else{
                    $tip = "服务器错误";
                    goto end;
                }
            }else{
                $tip = "帐号不存在";
                goto end;
            }
        }else{
            $tip = "帐号或邮箱错误";
            goto end;
        }
        end:
        return json_encode(['msg'=>$tip],JSON_UNESCAPED_UNICODE); 
    }

    /*  
    *   功能：修改密码表单
    *   http://121.201.34.37/index.php?r=mber/changemm&email=465703269@qq.com&zhao=10010316&key=857647d427c0dc08332c21927f545abf&token=458ee96a1b64acffadd30162c75e91b0
    */
    public function actionChangemm()
    {
        $tip    =   "";
        $email  =   @trim(Yii::$app->request->get('email'));
        $zhao   =   @trim(Yii::$app->request->get('zhao'));
        $key    =   @trim(Yii::$app->request->get('key'));
        $token  =   @trim(Yii::$app->request->get('token'));
        if ($email&&$zhao&&$key&&$token)
        {
            $soArr   =   [ 'u_xox_account'=>$zhao, 'u_email'=>$email, 'u_authKey'=>$key, 'u_accessToken'=>$token, 'u_lock'=>1 ];
            //$sql = Userinfo::find()->where($soArr)->createCommand()->getRawSql();echo $sql;die;
            $uData   =   @Userinfo::find()->where($soArr)->one();
            //var_dump($uData);die;
            if ($uData)
            {
                $scha   =   time() - (int)$uData['u_locktime'];
                if ($scha > 43200)
                {
                    echo "<center>该链接已经失效。必须在 12小时内修改。<a href='./index.php?r=mber/findmm&email=$email&hhjj=$zhao'>重新申请找回</a></center>";
                }else{
                    /*post 验证数据*/
                    if(Yii::$app->request->post("password")&&Yii::$app->request->post("repasswd")){
                        //echo 22222;die;
                        $email      =   @trim(Yii::$app->request->post('email'));
                        $zhao       =   @trim(Yii::$app->request->post('zhao'));
                        $key        =   @trim(Yii::$app->request->post('key'));

                        $token      =   @trim(Yii::$app->request->post('token'));
                        $password   =   @trim(Yii::$app->request->post('password'));
                        $repasswd   =   @trim(Yii::$app->request->post('repasswd'));
                        if ($email&&$zhao&&$key&&$token&&$password&&$repasswd&&strlen($repasswd)>=6&&strlen($repasswd)<=16)
                        {
                            if ($password===$repasswd)
                            {
                                $soArr   =   [ 'u_xox_account'=>$zhao, 'u_email'=>$email, 'u_authKey'=>$key, 'u_accessToken'=>$token, 'u_lock'=>1 ];
                                $uData   =   @Userinfo::find()->where($soArr)->one();

                                if ($uData)
                                {
                                    $scha   =   time() - (int)$uData['u_locktime'];
                                    if ($scha > 43200)
                                    {
                                        $tip    =   "time_fail";
                                    }else{
                                        
                                        //  初始值的方法
                                        //  $arr['u_authKey']       =   md5(md5($hao).$this->sj);
                                        //  $arr['u_accessToken']   =   md5(md5($hao).$this->domain);

                                        $currsj     =   time();
                                        $new_key    =   md5($zhao.$currsj);
                                        $new_token  =   md5($zhao.$email);
                                        if (@Userinfo::updateAll([ 'u_password' => md5($password), 'u_lock' => 0, 'u_locktime' => $currsj, 'u_authKey' => $new_key, 'u_accessToken' => $new_token ], "u_xox_account = $zhao"))
                                        {
                                            /*Yii::$app->session->setFlash("result","修改成功");
                                            return $this->refresh();*/
                                            echo "<center>修改密码成功</center>";die;
                                        }else{
                                            Yii::$app->session->setFlash("result","修改失败");
                                            return $this->render('changemm',['email'=>$email, 'zhao'=>$zhao, 'key'=>$key, 'token'=>$token ]);
                                        }                  
                                    }
                                }
                            }else{
                                Yii::$app->session->setFlash("result","两次输入的密码不一致");
                                return $this->render('changemm',['email'=>$email, 'zhao'=>$zhao, 'key'=>$key, 'token'=>$token ]);
                            }
                        }else{
                                //var_dump($email,$zhao,$key,$token,$password,$repasswd,strlen($repasswd));die;
                                Yii::$app->session->setFlash("result","检查两次输入的密码");
                                return $this->render('changemm',['email'=>$email, 'zhao'=>$zhao, 'key'=>$key, 'token'=>$token ]);
                        }
                    }else if(Yii::$app->request->post("password")||Yii::$app->request->post("repasswd")){
                                Yii::$app->session->setFlash("result","请检查密码和确认密码");
                    }else if(Yii::$app->request->post("password")!==Yii::$app->request->post("repasswd")){
                                Yii::$app->session->setFlash("result","密码和确认密码不一致");
                    }else if(Yii::$app->request->post()){
                                Yii::$app->session->setFlash("result","请输入密码和确认密码");
                    }
                        /**/
                    return $this->render('changemm',['email'=>$email, 'zhao'=>$zhao, 'key'=>$key, 'token'=>$token ]);                    
                }
            }else{
                echo "<center>数据验证错误。</center>";
            }
        }else{
            //var_dump($email,$zhao,$key,$token);
            echo "<center>数据验证错误</center>";
        }
    }

    /*  
    *   功能：修改密码实际处理程序
    */
    public function actionModifymm()
    {
        $tip    =   "fail";
        if (Yii::$app->request->isajax)
        {
            $email      =   @trim(Yii::$app->request->post('email'));
            $zhao       =   @trim(Yii::$app->request->post('zhao'));
            $key        =   @trim(Yii::$app->request->post('key'));

            $token      =   @trim(Yii::$app->request->post('token'));
            $password   =   @trim(Yii::$app->request->post('password'));
            $repasswd   =   @trim(Yii::$app->request->post('repasswd'));

            if ($email&&$zhao&&$key&&$token&&$password&&$repasswd&&strlen($repasswd)>=6&&strlen($repasswd)<=20)
            {
                if ($password===$repasswd)
                {
                    $soArr   =   [ 'u_xox_account'=>$zhao, 'u_email'=>$email, 'u_authKey'=>$key, 'u_accessToken'=>$token, 'u_lock'=>1 ];
                    $uData   =   @Userinfo::find()->where($soArr)->one();

                    if ($uData)
                    {
                        $scha   =   time() - (int)$uData['u_locktime'];
                        if ($scha > 43200)
                        {
                            $tip    =   "time_fail";
                        }else{
                            
                            //  初始值的方法
                            //  $arr['u_authKey']       =   md5(md5($hao).$this->sj);
                            //  $arr['u_accessToken']   =   md5(md5($hao).$this->domain);

                            $currsj     =   time();
                            $new_key    =   md5($zhao.$currsj);
                            $new_token  =   md5($zhao.$email);
                            if (@Userinfo::updateAll([ 'u_password' => md5($password), 'u_lock' => 0, 'u_locktime' => $currsj, 'u_authKey' => $new_key, 'u_accessToken' => $new_token ], "u_xox_account = $zhao"))
                            {
                                $tip   =    "oper_success";
                            }else{
                                $tip   =    "oper_fail";
                            }                  
                        }
                    }
                }else{
                    $tip   =    "2pass_not_same";
                }
            }else{
                $tip   =    "data_validate_fail";
            }

        }else{
            $tip   =    "no_ajax";
        }
        return json_encode(['tip'=>$tip]);
    }  

    /**
    * upload user avatar
    */      
    public function actionUpheadimg(){
        $allow_file_type = ['jpg', 'png', 'bmp', 'gif'];
        $this->layout= 'avatar';
        $uid = Yii::$app->request->get('uid');
        if(Yii::$app->request->isAjax){
            // $avatar_url = Yii::$app->request->get('avatar_url');
            $uid = Yii::$app->request->post('uid');
            if(empty($uid)){
                echo 'need login';die;
            }
            $avatar_params = Yii::$app->request->post('avatar_params');
            // print_r($_FILES);
            //upload user image
            if(!empty($_FILES['upload-file'])){
                $targetFolder = '../upload/avatar/';
                $sub_folder = date('y').DIRECTORY_SEPARATOR.date('m');
                
                // 目录不存在，创建目录
                if(!file_exists($targetFolder.$sub_folder)){
                    // mkdir($targetFolder.$sub_folder, 0666, true);
                    shell_exec('mkdir -p '. $targetFolder.$sub_folder);
                }
                $tempFile   =   $_FILES['upload-file'];
                $checkType = pathinfo($tempFile["name"],PATHINFO_EXTENSION);
                $tempFile = $_FILES['upload-file']['tmp_name'];
                // $targetPath = $_SERVER['DOCUMENT_ROOT'] . $targetFolder;
                
                $fileParts = pathinfo($_FILES['upload-file']['name']);
                if (in_array($fileParts['extension'], $allow_file_type)) {
                    $file_path = $targetFolder.$sub_folder;
                    $file_name =  date('ymdHis') . '_' . rand(1000,9999) .'.'. $fileParts['extension'];
                    // echo file_exists($tempFile);die;
                    $targetFile = rtrim($file_path,'/') . DIRECTORY_SEPARATOR . $file_name;

                    $up = move_uploaded_file($tempFile,$targetFile);
                    if($up && file_exists($targetFile)){
                        // $getID3 = new \getID3;
                        
                        // $ThisFileInfo = $getID3->analyze($targetFile);
                        // print_r($ThisFileInfo);die;
                        $m = Userinfo::find()->where(['u_id'=>$uid])->one();
                        $m->u_headportrait =  $sub_folder. DIRECTORY_SEPARATOR .$file_name;
                        $m->save();
                        
                       
                    }

                }else{
                    echo '格式不对';die;
                }
            }
           
            
            die;
        }
        
        
        
        return $this->render('headimg',[
            'uid'=>$uid]);
    }
    /**
    *播是玩修改密码
    *@time 2016年4月8日 10:11:44
    */
    public function actionEditpass(){
        /*
         * 检查用户的登录信息
         * */
        if(Yii::$app->user->isGuest){ //如果是访客的话
            if(!Yii::$app->request->get("logout")){
                $id = Yii::$app->request->get("uid");
                $md5pass = Yii::$app->request->get("pass");
                if ($id && $md5pass) {
                    $user = \app\models\Mber::find()->where(["uid" => (int)$id, 'password' => $md5pass])->one();
                    if ($user) {
                        @Yii::$app->user->login($user);
                    }
                }
            }
        }else{
            if(Yii::$app->request->get("logout")){
                if(Yii::$app->request->get("logout")=="1"){
                    Yii::$app->user->logout();
                }
            }
        }
        if(!Yii::$app->user->isGuest){
            $uid = Yii::$app->user->identity->uid;
            $model = Userinfo::findOne($uid);
            return $this->render("editpass");
        }
    }

    /**
    *@author 卢佳俊
    *@time 2016年4月8日 16:51:07
    *@func 修改密码
    */
    public function actionAjaxeditpass(){
        $errorCode = "1";
        //1:没有登录2：
        $msg = "没有登录";
        if(!Yii::$app->user->isGuest){
            $oldpass = Yii::$app->request->post("oldpass");
            $newpass = Yii::$app->request->post("newpass");
            $repass = Yii::$app->request->post("repass");
            $captcha_code = Yii::$app->request->post("captcha");
            //密码强度
            //$strength = check_user_password_strength($newpass);
            //captcha
            $captcha = new \yii\captcha\CaptchaValidator();
            $captcha->captchaAction = "mber/captcha";
            //var_dump($captcha->createCaptchaAction()->getVerifyCode());
            $code = $captcha->createCaptchaAction()->getVerifyCode();
            //两次输入的密码要一致
            if($newpass !=$repass){
                $errorCode = "1";
                $msg = "两次输入密码不一致";
                goto end;
            }

            //echo $strength;
            /*if($strength<60){
                $errorCode = "2";
                $msg = "密码强度太低了";
                goto end;
            }*/
            //和旧密码不能相同
            if($oldpass === $newpass){
                $errorCode = "3";
                $msg = "密码没有改变";
                goto end;
            }
            //获取用户信息
            //echo Yii::$app->user->identity->uid;
            $user = Userinfo::findOne(["u_id"=>(int)Yii::$app->user->identity->uid]);
            if(!$user){
                $errorCode = "4";//用户不存在
                $msg       = "用户不存在";
                goto end;
            }
            if($user["u_password"]!==md5($oldpass)){
                $errorCode = "5";//用户不存在
                $msg       = "原密码错误";
                goto end;
            }
            //验证码不正确
            //修改后
            if(!$captcha->createCaptchaAction()->validate($captcha_code,false)){
                $errorCode = "6";
                $msg = "验证码不正确";
                goto end;
            }

            $user->u_password = md5($newpass);
            if($user->update()){
                $errorCode = "0";
                $msg = "修改成功";
                goto end;
            }else{
                $errorCode = "7";
                $msg = "服务器修改失败";
                goto end;
            }
        }
        end:
        echo json_encode(["errorCode"=>$errorCode,"msg"=>$msg],JSON_UNESCAPED_UNICODE);
    }

    /**
    *@func 验证密码强度
    *@return json
    */
    public function actionPasswordstrength(){
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $val = Yii::$app->request->post("val");
        $strength  = check_user_password_strength($val);
        return ["strength"=>$strength];
    }

    /*  
    *   功能：测试程序
    */
    public function actionChangeok()
    {
        return $this->render('changeok');
    }

    /**
    *@func 找回密码
    *@return
    */
    public function actionForgetpass(){
        //if(!Yii::$app->user->isGuest){
            return $this->render("forgetpass");
        //}
    }

    /*  
    *   功能：测试程序
    */
    public function actionTmp()
    {
        echo check_user_password_strength("111111111");
    }





}

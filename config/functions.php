<?php
//自定义函数集

    /*  该函数功能：返回访问用户真实的ip
    *   
    */ 
    function get_real_ip()
    {
        $realip = '';
        $unknown = 'unknown';
        if (isset($_SERVER)){
            if(isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR']) && strcasecmp($_SERVER['HTTP_X_FORWARDED_FOR'], $unknown)){
                $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                foreach($arr as $ip){
                    $ip = trim($ip);
                    if ($ip != 'unknown'){
                        $realip = $ip;
                        break;
                    }
                }
            }else if(isset($_SERVER['HTTP_CLIENT_IP']) && !empty($_SERVER['HTTP_CLIENT_IP']) && strcasecmp($_SERVER['HTTP_CLIENT_IP'], $unknown)){
                $realip = $_SERVER['HTTP_CLIENT_IP'];
            }else if(isset($_SERVER['REMOTE_ADDR']) && !empty($_SERVER['REMOTE_ADDR']) && strcasecmp($_SERVER['REMOTE_ADDR'], $unknown)){
                $realip = $_SERVER['REMOTE_ADDR'];
            }else{
                $realip = $unknown;
            }
        }else{
            if(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), $unknown)){
                $realip = getenv("HTTP_X_FORWARDED_FOR");
            }else if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), $unknown)){
                $realip = getenv("HTTP_CLIENT_IP");
            }else if(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), $unknown)){
                $realip = getenv("REMOTE_ADDR");
            }else{
                $realip = $unknown;
            }
        }
        $realip = preg_match("/[\d\.]{7,15}/", $realip, $matches) ? $matches[0] : $unknown;
        return $realip;
    } 

    function get_location($ip)
    { 
        $url    =   "http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=json&ip=$ip";
        // 1. 初始化
        $ch = curl_init();
        // 2. 设置选项，包括URL
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        // 3. 执行并获取HTML文档内容
        $output = curl_exec($ch);
        // 4. 释放curl句柄
        curl_close($ch);
        
        $obj=json_decode($output);
        return $obj;
    }    

    /*  该函数功能：获取某用户全部硬件sn(whzh)
    *   
    *   根据用户uid返回该用户绑定的全部硬件sn号，返回的是数组
    *
    */     
    function getmbersnforuid($mberid)
    {
        if ($mberid){
            $narray = array();
            $mbersn = \app\models\UserinfoHadrwares::find()->asArray()->select('uh_seriesno')->where(['uh_uikey' => $mberid])->all();   
            foreach ($mbersn as $key => $value) {
                  array_push($narray,$value['uh_seriesno']);
            }  
            return $narray;   
        }else
            return "";
    }

    /*  该函数功能：根据指定的规则获取节点服务器domain
    *   
    *   rule 的值有如下： ipdizhi  /  shulian  /  specify
    *   sz 113.118.246.4
    *   sh 114.80.166.240
    *   bj 123.125.114.144
    */     
    function calus_domain($rule = 'shulian' , $param = '172.27.35.1')
    {
        $nsets   =  Yii::$app->params['nodeServer'];
        if ($rule == "shulian"){
            $mindm   =  '';
            $slian   =  10000000;
            $query2  =  new \yii\mongodb\Query;
            foreach ($nsets as $sname) {
                $tjian  =  [ 'u_domain' => $sname ];
                $nodes  =  $query2->from('userinfo')->where($tjian)->count("u_domain");
                if ($nodes < $slian){
                    $slian = $nodes;
                    $mindm = $sname;
                }
            }
            return $mindm;
        }elseif($rule == "specify"){
            if (in_array($param,$nsets)){
                return $param;
            }else{
                return $nsets[0];
            }
        }elseif($rule == "ipdizhi"){
            $city    =  @get_location($param)->city;
            $csets   =  Yii::$app->params['nodeCitync'];
            $csets   =  array_flip($csets);
            if (@$csets[$city]){
                return $csets[$city]; 
            }else{
                return $csets['深圳'];
            }
        }else{
            return $nsets[0];
        }
    }

    /*  
    *   该函数的功能：跳号
    *   当某个帐号碰到以下规则的时候，自动按指定的数字进行累加变号，
    *   目的是保留一些好的号码不被注册如 
    *   10000000 10010000 88888888 99999999 
    *   60080000 115700000 12345678 87654321
    */  
    function skiphao($hao = 1)
    {
        // if(preg_match('/[^\d]/',$input)) // 说明不为数字
        $thao   =   1;  //默认为1，代表可用，0为已用,2代表预留
        if(preg_match('/(?:(?:0(?=1)|1(?=2)|2(?=3)|3(?=4)|4(?=5)|5(?=6)|6(?=7)|7(?=8)|8(?=9)){3,}|(?:9(?=8)|8(?=7)|7(?=6)|6(?=5)|5(?=4)|4(?=3)|3(?=2)|2(?=1)|1(?=0)){3,})\d/i',$hao)){
            $thao   =   2;
        }else{
            if(preg_match('/([\d])\1{2,}/i',$hao)){
                $thao   =   2;
            }else{
                if(preg_match('/([\d])\1{1,}([\d])\2{1,}/i',$hao)){
                    $thao   =   2;
                }else{
                    if(preg_match('/(([\d]){1,}([\d]){1,})\1{1,}/i',$hao)){
                        $thao   =   2;
                    }                    
                } 
            }   
        }   
        return $thao;
    }

    /*  
    *   该函数的功能：登录或登出成功后写入或更新本机MongoDB日志
    *   当用户登录成功后，将用户的uid,logintime,loginouttime,ip,device,sessionid等信息写入MongoDB文档
    *   当用户登出成功后，将用户的loginouttime等信息写入MongoDB文档
    */  
    function loginlog($uid = '', $logintime = '', $device = '', $method = 'login')
    {
        if ($method == 'login')
        {
            $session        =   Yii::$app->session;
            $session->open();
            $sid            =   $session->getId();  

            $i              =   0;
            $newArr         =   [];
            $fieldArr       =   ['uid','logintime','logouttime','ip','device','sessionid'];
            $collection     =   Yii::$app->mongodb2->getCollection('userloginlog');
            if (!$collection->findOne(['sessionid'=>$sid])&&$uid)
            {                
                $session->set('logintime', time());
                $newArr['uid']          =  (int)$uid;
                $newArr['logintime']    =  $session->get('logintime');
                $newArr['logouttime']   =  $session->get('logintime') + 58;

                $newArr['ip']           =  get_real_ip();
                $newArr['device']       =  $device;
                $newArr['sessionid']    =  $sid;
                $newArr['onlinetime']   =  58;

                $collection->insert($newArr);
            }
        }

        if ($method == 'logout')
        {
            $currsjc        =   time();
            $dns            =   Yii::$app->mongodb2->dsn; 
            $mongoClient    =   new \MongoClient($dns);
            $collection     =   $mongoClient->xoxdb->userloginlog; 
            $where          =   [ 'uid' => (int)$uid, 'logintime' => (int)$logintime ];
            $newdata        =   [ 'logouttime'=>$currsjc , 'onlinetime'=>$currsjc - $logintime ];
            $result         =   $collection->update($where, ['$set'=>$newdata]);
        }

    }

    /*
    *   计算空闲的音效服务器，并返回json
    */
    function calculator_free_effectserver($la){
        $la         =   $la?$la:1;
        // $freeserver =   EffectServer::model()->find(array('select'=>'es_ip,es_port','condition'=>'es_languageikey = :la','params'=>array(':la'=>$la),'order'=>'es_number asc'));
        // if ($freeserver){
        //     $ip     =   $freeserver['es_ip'];
        //     $port   =   $freeserver['es_port'];
        // }else{
        //     $ip     =   "";
        //     $port   =   "";
        // }
        // return CJSON::encode(array('ip'=>$ip,'port'=>$port));
        $tjian      =   [ 'langid' => "$la" ]; 
        $query      =   new \yii\mongodb\Query;
        $data       =   $query->select([ '_id'=>0, 'ip'=>1, 'port'=>1, 'langid'=>1, 'efnum'=>1 ])->from('effectserverlist')->where($tjian)->orderBy('efnum asc')->one();
        if ($data)
            return \yii\helpers\Json::encode([ 'ip'=>$data['ip'], 'port'=>$data['port'], 'langid'=>$data['langid'] ]); 
        else
            return \yii\helpers\Json::encode([ 'ip'=>'', 'port'=>'', 'langid'=>'' ]); 
    }    

    //此函数的功能是根据秒数换算成天时分秒，比如4000秒=？天？时？分？秒
    function formatSeconds($time, $fs = "1") 
    { 
        
        $sec=time()-$time;
        if($sec>=0){
            $year =floor($sec/(24*3600*365));
            $days = floor($sec / (24*3600)); 
            $sec = $sec % (24*3600); 
            $hours = floor($sec / 3600); 
            $remainSeconds = $sec % 3600; 
            $minutes = floor($remainSeconds / 60); 
            $seconds = intval($sec - $hours * 3600 - $minutes * 60); 
            if ( $fs == "1" ){
                return $days."天".$hours."小时".$minutes.'分钟';
            }else{
                if($year>0){
                    return $year.'年前';
                }
                if($days>0){
                    return $days."天前";
                }
                if($hours>0){
                    return $hours."小时前";
                }
                if($minutes>0){
                    return $minutes."分钟前";
                }
                else{
                    return "刚刚"; 
                }
            }
        }
    }  

    /*
    *   该函数功能：加密函数(通过验证函数可以知道某个请求是否合法)
    *
    *   验证函数 算法是 先任取2个值，拼接后再md5，再取md5码之后的15个字符，最后对这15个字符再md5
    */
    function jiami($v1,$v2,$sid='')
    {
        if ($sid==""){
            @session_start();
            $sid    = session_id();
        }

        $sm     =   @md5($v1.$v2);
        $sm     =   @substr($sm,0,15);
        $sm     =   @md5($sm.$sid);
        return $sm;
    }

/*
    * 功能：分页
    */
    function outputPage($countPage,$lxuPage=3,$current=1,$reccount,$inputTxt=false)
    {
        if ($countPage>1){
            $js  = "";
            // $js .="<script>";
            // $js .="$('.page').each(function(index){";
            // $js .="   $(this).click(function(){";
            // $js .="      pagev = $('.page:eq('+index+')').html();"; 
            // $js .="      alert(pagev);";
            // $js .="      ";
            // $js .="      ";                                  
            // $js .="   });";
            // $js .="});";
            // $js .="</script>";      
            //当前页码是 $current
            //获取连续页面的折中数,该连续页面一般为奇数 
            //   1   2   3   4   5   6   7   8   9   ← 假设连续页为 9  floor(9/2)=5 这便是要获取的折中数
            //                   ▲                   ← 5便是折中点
            $zheZhongshu        =       floor($lxuPage / 2);
            
            $pageStr            =       "<li><span class=\"tongji\">第 $current 页</span></li>";
            $toshu              =       0;
            //出现这种分页效果的要求是：总页数必须大于连续数，注意是大于 >
            if ($countPage > $lxuPage){
                //如果当前页不大于折中页的话，那就输出 1 到 折中数 + 折中数 之间的页面
                if ($current < $lxuPage){
                    for($vi=1;$vi<=$lxuPage;$vi++){
                        $currentpCss = ($current == $vi)?"selected":"";
                        $pageStr    .= "<li><a class=\"page $currentpCss\">$vi</a></li>";
                    }

                    if (($countPage - $lxuPage)<= $lxuPage )
                        for($vi=$lxuPage+1;$vi<=$countPage;$vi++){
                            $pageStr    .= "<li><a class=\"page\">$vi</a></li>";
                        }                        
                    else
                        $pageStr    .= "<li><span class=\"ddd\">...</span></li><li><a class=\"page\">$countPage</a></li>";


                }else{//如果当前页大于折中页的话，那就输出 1 ... ($current-$zheZhongshu) $current ($current-$zheZhongshu) ...$countPage 之间的页面
                    // 不大于折中页数又分2种情况，第一种是等于，另一种是大于
                    if ($current == $countPage){
                        $pageStr    .= "<li><a class=\"page\">1</a></li><li><span class=\"ddd\">...</span></li>";
                        $beginPage   = $countPage - $zheZhongshu;
                        for($vi=$beginPage;$vi<=$countPage;$vi++){
                            $currentpCss = ($current == $vi)?"selected":"";
                            $pageStr    .= "<li><a class=\"page $currentpCss\">$vi</a></li>";
                        }   
                    }else{                  
                        $beginPage   = $current - $zheZhongshu;
                        $endPage     = $current + $zheZhongshu;
                        $endPage     = $endPage>$countPage?$countPage:$endPage;
                        if ($beginPage == 2)
                            $pageStr    .= "<li><a class=\"page\">1</a></li>";
                        else
                            $pageStr    .= "<li><a class=\"page\">1</a></li><li><span class=\"ddd\">...</span></li>";
                        for($vi=$beginPage;$vi<=$endPage;$vi++){
                            $currentpCss = ($current == $vi)?"selected":"";
                            $pageStr    .= "<li><a class=\"page $currentpCss\">$vi</a></li>";
                        }
                        if (($countPage - $current) <= $zheZhongshu){
                        }else{
                            if ($endPage == $countPage - 1)
                                $pageStr    .= "<li><a class=\"page\">$countPage</a></li>";
                            else
                                $pageStr    .= "<li><span class=\"ddd\">...</span></li><li><a class=\"page\">$countPage</a></li>";
                        }
                    }
                }
            }else{
                for($vi=1;$vi<=$countPage;$vi++){
                    $currentpCss = ($current == $vi)?"selected":"";
                    $pageStr    .= "<li><a class=\"page $currentpCss\">$vi</a></li>";
                }   
            }
            // $pageStr    .=  "<li><span class=\"tongji\">共 $countPage 页</span></li><li><span class=\"tongji\">共 $reccount 条记录</span></li>";
            $pageStr    .=  "<li><span class=\"tongji\">共 $countPage 页</span></li>";
            return "<ul class=\"pagemaCss\">$pageStr</ul>$js";  
        }else{
            return "";//说明总页数为0，输出为空串
        }
    }

    /*
    *  计算周的开始和结束时间
    */
    function get_weekBE() 
    {
        $benzhou        =   [];  
        //当前日期
        $sdefaultDate   =   date("Y-m-d");
        //$first =1 表示每周星期一为开始日期 0表示每周日为开始日期
        $first          =   1;
        //获取当前周的第几天 周日是 0 周一到周六是 1 - 6
        $w              =   date('w',strtotime($sdefaultDate));
        //获取本周开始日期，如果$w是0，则表示周日，减去 6 天
        $week_start     =   date('Y-m-d',strtotime("$sdefaultDate -".($w ? $w - $first : 6).' days'));
        $benzhou[]      =   strtotime($week_start);
        //本周结束日期
        $week_end=date('Y-m-d',strtotime("$week_start +6 days"));
        $benzhou[]      =   strtotime($week_end);
        return $benzhou;
    }

    /*
    *  获取省(直辖市)名称
    */
    function getShenname($ikey)
    {
        if (!$ikey){
            return "";
        }else{
            $ikey       =   (int)$ikey;
            $condition  =   [ 'provinceID' => $ikey ];
            $collection =   Yii::$app->mongodb2->getCollection('province');
            $data       =   $collection->findOne($condition);         
            return $data['provinceName'];
        }
    } 

    /*
    *  获取市名称
    */
    function getCityname($ikey)
    {
        if (!$ikey){
            return "";
        }else{
            $ikey       =   (int)$ikey;
            $condition  =   [ 'cityID' => $ikey ];
            $collection =   Yii::$app->mongodb2->getCollection('city');
            $data       =   $collection->findOne($condition);         
            return $data['cityName'];
        }
    }

    /*
    *  获取生肖
    */    
    function getSxiao($key = 0)
    {
        
        if ($key > 12) $key = 11;
        if ($key){  
            $arr    =   array('鼠','牛','虎','兔','龙','蛇','马','羊','猴','鸡','狗','猪');
            return $arr[$key];          
        }else{
            return ""; 
        }
    }

    /*
    *  获取星座
    */
    function getStar($key = 0)
    {
        if ($key > 12) $key = 11;
        if ($key){  
            $arr    =   array('水瓶座','双鱼座','白羊座','金牛座','双子座','巨蟹座','狮子座','处女座','天秤座','天蝎座','射手座','摩羯座');
            return $arr[$key];
        }else{
            return ""; 
        }           
    }

    /*
    *  获取血型
    */
    function getXing($key = 0)
    {
        if ($key > 5) $key = 4;
        if ($key){  
            $arr    =   array('A','B','O','AB','其他');
            return $arr[$key];
        }else{
            return ""; 
        }           
    }

    /*
    *  获取职业
    */
    function getZiye($key = 0)
    {
        if ($key > 6) $key = 5;
        if ($key){  
            $arr    =   array('在校学生','固定工作者','自由职业者','失业/待业/无业','退休','其他');
            return $arr[$key];
        }else{
            return ""; 
        }               
    }

    /*
    *  获取学历
    */
    function getXueli($key = 0)
    {
        if ($key > 8) $key = 7;
        if ($key){  
            $arr    =   array('小学及以下','初中','高中','中专','大专','本科','研究生','博士及以上');
            return $arr[$key];
        }else{
            return ""; 
        }           
    }

    /*
    *  根据ikey获取音效师等级
    */
    function getYinxiaoerGradefornum($num)
    {
        if (!$num){
            return "";
        }else{
            switch ($num)
            {
                case 1:
                    $yxer = "音效小妖";
                    break;  
                case 2:
                    $yxer = "音效精灵";
                    break;
                case 3:
                    $yxer = "音效骑士";
                    break;
                case 4:
                    $yxer = "音效天使";
                    break;
                case 5:
                    $yxer = "音效魔王";
                    break; 
                case 6:
                    $yxer = "音效老妖";
                    break; 
                case 7:
                    $yxer = "音效大帝";
                    break;                                  
                default:
                    $yxer = "未知等级";
            }  
            return $yxer;          
        }
    }

    /*
    *  根据uid获取会员的hhjjhao
    */
    function getHhjj($uid)
    {
        if (!$uid){
            return "";
        }else{
            $condition  =   [ 'u_id' => $uid ];
            $collection =   Yii::$app->mongodb->getCollection('userinfo');
            $data       =   $collection->findOne($condition);         
            return $data['u_xox_account'];
        }
    } 

    /*
    *  根据uid获取会员的昵称
    */
    function getNick($uid)
    {
        if (!$uid){
            return "";
        }else{
            $condition  =   [ 'u_id' => $uid ];
            $collection =   Yii::$app->mongodb->getCollection('userinfo');
            $data       =   $collection->findOne($condition);         
            return @$data['u_nickname'];
        }
    }     

    /*
    *  根据uid获取会员的音效总数量
    */
    function getCountforUid($uid)
    {
        if (!$uid){
            return "";
        }else{
            $condition      =   [ 'ei_make_ikey' => $uid];
            $dns            =   Yii::$app->mongodb2->dsn; 
            $mongoClient    =   new \MongoClient($dns);
            $collection     =   $mongoClient->xoxdb->effectinfo;
            $recCount       =   $collection->count($condition);            
            return $recCount;
        }
    }

    /*
    * 功能： 获取某个音效的总下载次数
    */
    function getDowntimes($yxikey, $siduan = 1){
        if (!$yxikey){
            return "";
        }else{
            $dns            =   Yii::$app->mongodb2->dsn;  
            $mongoClient    =   new \MongoClient($dns);
            $collection     =   $mongoClient->xoxdb->effect_down_recs;
            $siduan>4?$siduan=1:$siduan;
            $siduan<1?$siduan=1:$siduan;
            if ($siduan == 1){          // 今天
                $d_00       =   strtotime(date('Y-m-d',time()));
                $d_24       =   $d_00 + 24*60*60 - 1;                  
                $condition  =   [ 'yxikey' => (int)$yxikey, 'd_time' => ['$lte' => $d_24, '$gte' => $d_00] ];
            }else if ($siduan == 2){    // 本周
                $w_00       =   get_weekBE()[0];
                $w_07       =   get_weekBE()[1] + 24*60*60 - 1; 
                $condition  =   [ 'yxikey' => (int)$yxikey, 'd_time' => ['$lte' => $w_07, '$gte' => $w_00] ];
            }else if ($siduan == 3){    // 本月
                $m_00       =   mktime(0,0,0,date('m'),1,date('Y'));
                $m_30       =   mktime(23,59,59,date('m'),date('t'),date('Y'));
                $condition  =   [ 'yxikey' => (int)$yxikey, 'd_time' => ['$lte' => $m_30, '$gte' => $m_00] ];
            }else if ($siduan == 4){    // 全部
                $condition  =   [ 'yxikey' => (int)$yxikey ];
            }
            $recCount       =   $collection->count($condition);  
            return $recCount;
        }        
    }     

    /*
    *  根据ikey获取音效名称
    */
    function getYinxiaonameforikey($ikey)
    {
        if (!$ikey){
            return "";
        }else{
            $condition      =   [ 'ikey' => (int)$ikey];
            $dns            =   Yii::$app->mongodb2->dsn;
            $mongoClient    =   new \MongoClient($dns);
            $collection     =   $mongoClient->xoxdb->effectinfo;
            // $recData        =   $collection->find($condition)->fields(['_id'=>0, 'uid'=>0, 'yxikey'=>0, 'd_time'=>1, 'pays'=>0, 'paystatus'=>0]);          
            $recData        =   $collection->find($condition);          
            foreach ($recData as $value) {
                $dataArr[] =   $value;
            }            
            return $dataArr[0]['ei_filename'];
        }
    }

    /*
    *  根据uikey获取是否为音效师
    */
    function isyxiaoerforuikey($uikey)
    {
        if (!$uikey){
            return "";
        }else{
            $condition      =   [ 'er_uid' => (int)$uikey ];
            $dns            =   Yii::$app->mongodb2->dsn;
            $mongoClient    =   new \MongoClient($dns);
            $collection     =   $mongoClient->xoxdb->effecter;
            $recData        =   $collection->find($condition)->fields([ '_id'=>0, 'er_uid'=>1 ]);          
            $dataArr        =   [];
            foreach ($recData as $value) {
                $dataArr[] =   $value;
            }            
            return count($dataArr);
        }
    }    
    
    /**
    * 生成订单号
    */ 
    function genOrder($type = 1, $ikey = 0, $uid)
    {
        $ostr    =   "";

        $types=[
            0=>'CZ',
            1=>'JY',
        ];

        if ($uid&&$uid>0){
            $ostr    =   $types[$type].date('YmdHis').$ikey.$uid.rand(10000,99999);
        }
        return  $ostr;
    } 

    function checkEmail($Argv)   
    {   
        $RegExp='/^[a-z0-9][a-z\.0-9-_]+@[a-z0-9_-]+(?:\.[a-z]{0,3}\.[a-z]{0,2}|\.[a-z]{0,3}|\.[a-z]{0,2})$/i';
        return preg_match($RegExp,$Argv)?1:0;
    } 

    function checkMobile($phonenumber)   
    {  
        $isok = 0;
        if(preg_match("/1[3458]{1}\d{9}$/",$phonenumber)){
            $isok = 1;
        }
        return $isok;
    } 

    function Sec2Time($time)
    {
        if(is_numeric($time)){
            $value = array(
              "years" => 0, "days" => 0, "hours" => 0,
              "minutes" => 0, "seconds" => 0,
            );
            if($time >= 31556926){
              $value["years"] = floor($time/31556926);
              $time = ($time%31556926);
            }
            if($time >= 86400){
              $value["days"] = floor($time/86400);
              $time = ($time%86400);
            }
            if($time >= 3600){
              $value["hours"] = floor($time/3600);
              $time = ($time%3600);
            }
            if($time >= 60){
              $value["minutes"] = floor($time/60);
              $time = ($time%60);
            }
            $value["seconds"] = floor($time);
            // $t=$value["years"] ."年". $value["days"] ."天"." ". $value["hours"] ."小时". $value["minutes"] ."分".$value["seconds"]."秒";
            $t = $value["days"]."天".$value["hours"] ."小时". $value["minutes"] ."分".$value["seconds"]."秒";
            Return $t;
        
        }else{
            return '';
        }
    }

    /*
    * 
    * 发送邮件
    *
    */    
    function sendmail($email, $subject, $content)
    {
        $status = 0;
        if (checkEmail($email)&&$content)
        {
            $mail= Yii::$app->mailer->compose();   
            $mail->setTo($email);  
            $mail->setSubject($subject);  
            $mail->setHtmlBody($content);    //发布可以带html标签的文本
            if($mail->send())  
                $status = 1;  
        }
        return $status;
    }

    /*
    * 时间单位为秒
    * 在线时长满两个小时 活跃天数为0.5天
    * 活跃天数换算为等级
    * 公式 (根号time+4)-2
    * */
    function haoDengji($time)
    {
        $huoyue_tianshu     =   (($time/3600)/2)/2;
        $str    =   "";
        $host   =   Yii::$app->request->hostInfo;
        if($huoyue_tianshu>=5){
            $s = floor(sqrt($huoyue_tianshu+4)-2);
            $huang  =   $s/64;//皇冠的个数
            for($i=1;$i<=$huang;$i++){
                $str.="<img src=\"$host/images/huang.png\">";
            }
            $s   =   $s%64;//剩余星星的数量
            $taiyang    =   $s/16;//太阳的数量
            for($i=1;$i<=$taiyang;$i++){
                $str.="<img src=\"$host/images/taiyang.png\">";
            }
            $s  =   $s%16;//剩下星星的数量
            $taiyang    =   $s/4;//月亮的数量
            for($i=1;$i<=$taiyang;$i++){
                $str.="<img src=\"$host/images/moon.png\">";
            }
            $s  =   $s%4;//剩余星星的数量
            for($i=1;$i<=$s;$i++){
                $str.="<img src=\"$host/images/star.png\">";
            }
        }else{
            $str    ="活跃天数".floor($huoyue_tianshu)."天";
        }
        return $str;
    }

    /*
    *  用户行为记录
    *  备注：3分钟之内访问同一页面只记录一次
    *  2015-9-7 13:10 by W.one
    */       
    function user_action_log($uid=0, $seid='', $controller='', $action='', $things='', $parameter='')
    {
        $tip =  "fail";
        if ($seid&&$controller&&$action)
        {
            $dns            =   Yii::$app->mongodb2->dsn;
            $mongoClient    =   new \MongoClient($dns);
            $collection     =   $mongoClient->xoxdb->user_action_log;
            
            $d_01           =   time();
            $d_02           =   $d_01 - 30;             
            $conditionArr1  =   [ 'ual_seid' => $seid, 'ual_controller' => $controller, 'ual_action' => $action, 'ual_things' => $things ];
            $conditionArr2  =   [ 'ual_intotime' => ['$lte' => $d_01, '$gte' => $d_02 ] ];
            $conditionArr   =   [ '$and'   => [ $conditionArr1, $conditionArr2 ]];

            $recCount       =   $collection->count($conditionArr);
            $tip = $recCount;
            if ($recCount == 0){
                $newArr                     =    [];

                $newArr['ual_seid']         =    $seid;
                $newArr['ual_uid']          =    (int)$uid;

                $newArr['ual_intotime']     =    time();
                $newArr['ual_controller']   =    $controller;

                $newArr['ual_action']       =    $action;
                $newArr['ual_things']       =    $things;

                $newArr['ual_csu']          =    $parameter;

                $result                     =    $collection->insert($newArr);
                if ($result){
                    $tip =  "success";
                }
            }else{
                $tip =  "exists";
            } 
        }else{
            $tip =  "data_err";
        }
        return $tip;
    }
    function log_for_user($word='', $type='log') {
        
        $logname = Yii::$app->getRuntimePath() . '/logs/'.$type.'.log';
        $fp = fopen($logname,"a");
        flock($fp, LOCK_EX) ;
        fwrite($fp,sprintf("{$type}|time:%s|msg:%s\n",strftime("%Y-%m-%d %H:%M:%S",time()),$word));
        flock($fp, LOCK_UN);
        fclose($fp);
    }

    function check_user_password_strength($str){
        /*$h = 0;
        $size = strlen($str);
        foreach(count_chars($str,1) as $v){
            $p = $v/$size;
            $h -= $p*log($p)/log(2);


        }
        $strength = ($h/4)*100;
        if($strength>100)
            $strength = 100;
        return $strength;*/

        //九位一下单一字符都是弱
        //九位以上的单一字符就是一般
        //九位以上的三三组合
        $size = strlen($str);
        if($size<9){
            //echo $str;
            if(preg_match_all('/^(\d){5,8}$/',$str,$matches)){                  //九位一下全部数字
                //echo "全部数字";
                return 59;
            }elseif (preg_match_all('/^[a-zA-Z]{5,8}$/', $str, $matches)) {     //九位一下全部字母
                //echo "全部字母";
                return 59;
            }elseif(preg_match_all('/^[^\w]{5,8}$/', $str, $matches))   {       //九位一下非数字字母
                //echo "全部符号";
                return 59;
            }elseif(preg_match_all('/^[a-zA-Z\d]{6,8}$/', $str, $matches)){                      //字母数字组合 为一般
                //echo "数字字母组合";
                return 79;
            }elseif(preg_match_all('/^[(\~\`\!\@\#\$\%\^\&\*\(\)\{\}\[\]\<\>\?\,\.\/\:\"\|\;\'\|\;\'\\)a-zA-Z]{6,8}$/', $str, $matches)){                      //字母符号组合 为一般
                //echo "字母符号组合";
                return 79;
            }elseif(preg_match_all('/^[(\~\`\!\@\#\$\%\^\&\*\(\)\{\}\[\]\<\>\?\,\.\/\:\"\|\;\'\|\;\'\\)0-9]{6,8}$/', $str, $matches)){                      //字母符号组合 为一般
                //echo "数字符号组合";
                return 79;
            }elseif(preg_match_all('/^[(\~\`\!\@\#\$\%\^\&\*\(\)\{\}\[\]\<\>\?\,\.\/\:\"\|\;\'\|\;\'\\)a-zA-Z0-9]{6,8}$/', $str, $matches)){                      //字母符号组合 为一般
                //echo "三三组合";
                return 99;
            }
        }else{
            if(preg_match_all('/^(\d){9,}$/',$str,$matches)){                  //九位一下全部数字
                //echo "全部数字";
                return 79;
            }elseif (preg_match_all('/^[a-zA-Z]{9,}$/', $str, $matches)) {     //九位一下全部字母
                //echo "全部字母";
                return 79;
            }elseif(preg_match_all('/^[^\w]{9,}$/', $str, $matches))   {       //九位一下非数字字母
                //echo "全部符号";
                return 79;
            }elseif(preg_match_all('/^[a-zA-Z\d]{9,}$/', $str, $matches)){                      //字母数字组合 为一般
                //echo "数字字母组合";
                return 99;
            }elseif(preg_match_all('/^[(\~\`\!\@\#\$\%\^\&\*\(\)\{\}\[\]\<\>\?\,\.\/\:\"\|\;\'\|\;\'\\)a-zA-Z]{9,}$/', $str, $matches)){                      //字母符号组合 为一般
                //echo "字母符号组合";
                return 99;
            }elseif(preg_match_all('/^[(\~\`\!\@\#\$\%\^\&\*\(\)\{\}\[\]\<\>\?\,\.\/\:\"\|\;\'\|\;\'\\)0-9]{9,}$/', $str, $matches)){                      //字母符号组合 为一般
                //echo "数字符号组合";
                return 99;
            }elseif(preg_match_all('/^[(\~\`\!\@\#\$\%\^\&\*\(\)\{\}\[\]\<\>\?\,\.\/\:\"\|\;\'\|\;\'\\)a-zA-Z0-9]{9,}$/', $str, $matches)){                      //字母符号组合 为一般
                return 99;
            }
        }
    }
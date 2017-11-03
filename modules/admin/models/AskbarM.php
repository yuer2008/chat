<?php

namespace app\models;

use Yii;
use yii\mongodb\Query;
use app\models\Userinfoaccountmongo;

/**
 * This is the model class for table "{{%askbar_type}}".
 * 问题表
 * @property integer $ikey
 * @property integer $pt_parent_ikey
 * @property string $pt_name
 * @property string $pt_icon
 * @property integer $pt_order
 */
class AskbarM extends \yii\mongodb\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function collectionName()
    {
        return 'askbar';
    }

    public function attributes(){
        return ['_id', 'title', 'type', 'describle', 'uid', 'reward', 'add_time','clicks_num', 'is_top', 'answer_num', 'is_delete','ask_id'];
    }

    // public $ab_title;
    // public $ab_describle;
    // public $ab_type;
    // public $ab_uikey;
    // public $ab_reward;
    public $verifyCode;
    // public $ab_time;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
          
        ];
    }
    /**
     * Validates the ab_reward.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validateReward($attribute, $params)
    {
        if (!$this->hasErrors()) {
            // $user = $this->getUser();
            $valid = 100;
            if ($attribute>$valid) {
                $this->addError($attribute, '有效经验值');
            }
        }
    }

    /**
     * @return 增加访问量+1
     */
    public static function addClicknum($key){
        $m = self::findOne($key);
        $m->clicks_num = $m->clicks_num+1;
        $m->update();
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'ab_title' => '问题标题',
        ];
    }

    /**
     * 题库列表
     * @param 参数
     * @return array
    */
    public static function getAskList($param){
        $cont = ['and',['is_delete'=>0]];
        

        if(!empty($param['keyword'])){
            $cont2 = [['like','title',$param['keyword']]];
            $cont = array_merge($cont,$cont2);
        }
        if(!empty($param['limit'])){
            $cont3= [['BETWEEN','add_time',time()-24*3600,time()]];
            $cont = array_merge($cont,$cont3);
        }

        // if(!empty($param['type_level_2'])){
        //     $cont['type'] = (int)$param['type_level_2'];
        // }

        if(!empty($param['type_level_1'])){
            $sub = AskbarType::getSubTypes((int)$param['type_level_1']);
            $cont1 = ['type'=>['$in'=>array_values($sub)]];
            $cont = array_merge($cont,$cont1);
           
        }
        if(!empty($param['reward'])){
            $cont2=['reward'=>['$gt'=>0]];
            $cont = array_merge($cont, $cont2);  
        }
// print_r($cont);die;
        $data = self::find()->where($cont)->orderBy('add_time desc');
        return $data;
    }

    /**
     * 回答列表
     * @return array
    */
    public static function getAnsweredData($ikey){
        if(empty($ikey)){
            return;
        }
        $data = self::find()->where('ab_isbestanswer="0" and ab_toaskikey=:ab_toaskikey',array(':ab_toaskikey'=>$ikey))->orderBy('ab_time desc');
        
        return $data;
    }

    /**
     * 我的问题 - list
     * @return array
    */
    public static function my($uid){
        if(empty($uid)){
            return;
        }
        $data = self::find()->where(['is_delete'=>0,'uid'=>(int)$uid]);
       
        return $data;
    }

    /**
     * 我回答过的问题-list
     * @return array
    */
    public  function myAnswer($uid){
        if(empty($uid)){
            return;
        }
        $ids = Answer::userAnswered($uid);

        $data = self::find()->where(['ask_id'=>['$in'=>$ids]])->andWhere(['is_delete'=>0]);
        // $data = self::find()->where(['in', '_id', $ids])->andWhere(['is_delete'=>0]);
        $query = new Query;
        $query->select(['title'])
            ->from(self::collectionName())
            // ->where(['ask_id'=>['\$in'=>$ids]])
            // ->where(['_id'=>ObjectId('555c91f0e7bfbcea078b456d')])
            // ->where(['in','ask_id',$ids])
            ->andWhere(['is_delete'=>0]);
        // $data = $query->all();    
        return $data;
    }
    /**
     * 我回答过的问题个数
     * @param $uid 用户id
     * @return int
    */
    public  function myAnswerNumber($uid){
        if(empty($uid)){
            return;
        }
        $ids = Answer::userAnswered($uid);
        return count($ids);
    }    

    /**
     * 删除问题，update is_delete
     * @return array
    */
    public static function remove($key){
        // $rs = self::find()->modify(['_id'=>$key],['is_delete'=>1]);
        $data = self::findOne($key);
        $data->is_delete = 1;
        return $data->update(false);
    }

    /**
     * update问题的回答数目
     * 
    */
    public static function updateAnswerNum($key){
        $m = self::findOne($key);
        $m->answer_num = $m->answer_num+1;
        $m->update();
    }
    /**
     * 活跃用户
     * @param $num 元素个数
     * @return array
     * 
    */
    public static function getActiveUser($num=1){
        $uid = [];
        $query = new Query;
        $query->from = self::collectionName();
        $query->select = ['uid'];
        $query->orderBy = ['add_time'=>SORT_DESC];
      
        $rows = $query->limit(100)->all();
        
        
        foreach($rows as $k=>$v){
            if(empty($v['uid']))continue;
            $uid[] = (int)$v['uid'];
        }

        // $collection = Yii::$app->mongodb->getCollection(self::collectionName());
        // $data = $collection->find()->distinct('uid');
        $uid =  array_values(array_unique($uid)); 
        $uid = array_slice($uid,0,$num);
        $data = Userinfoaccountmongo::find()->where(['xn_uid'=>['$in'=>$uid]])->asArray()->all();
           
        return $data;
    }
    /**
     * 检查用户是否已经回答过了
     * @param $uid 用户id
     * @param $key 问题id
     * @return 已回答true or false
    */
    public static function checkAnswered($uid, $key){
        if(empty($uid)){
            return true;
        }
        $m = Answer::findOne(['uid'=>$uid,'ask_id'=>$key, 'is_delete'=>0]);
        if(empty($m)){
            return false;
        }else{
            return true;
        }
    }
    /**
     * 已获得回答的问题个数
     * @return int
     */
    public static function getAnsweredNum(){
        $answered_count = Yii::$app->redis->get('answered_count');
        if(empty($answered_count)){
            $answered_count = AskbarM::find()->where(['answer_num'=>['$gt'=>0]])->count();
            //设置10s的缓存
            Yii::$app->redis->setex('answered_count',10,$answered_count);    
        }
        return $answered_count;
    }
    /**
    * 为所有问题更新回答数，回答数出错时可用来更新
    */
    public static function answerNumStatUpdate(){
        $all = self::getAskList([]);
        $data = $all->all();
        foreach($data as $k=>$v){
            $d = Answer::find()->where(['ask_id'=>$v->ask_id,'is_delete'=>0])->all();
            $m = self::findOne($v->_id);
            $m->answer_num=count($d);
            $m->update();
        }
    }

}

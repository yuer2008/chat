<?php

namespace app\modules\admin\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%askbar_type}}".
 *
 * @property integer $ikey
 * @property integer $pt_parent_ikey
 * @property string $pt_name
 * @property string $pt_icon
 * @property integer $pt_order
 */
class AskbarType extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%askbar_type}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['pt_parent_ikey', 'pt_order'], 'integer'],
            [['pt_name'], 'string', 'max' => 60]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ikey' => 'Ikey',
            'pt_parent_ikey' => 'pt_parent_ikey',
            'pt_name' => 'pt_name',
            'ab_createtime' => 'Ab Createtime',
            'pt_icon' => 'pt_icon',
            'pt_order' => 'pt_order'
        ];
    }
   
    /**
     * @ 获取分类id和名称,提供分类下拉框数据
     * @return array ikey=>name
    */
    public static function getTypes(){

        
        $data = AskbarType::find()->all();
        $new_data = ArrayHelper::toArray($data);
        $all = '';
        $level_1 = '';
        foreach($new_data as $k=>$v){
            if($v['pt_parent_ikey'] == ''){
                $level_1[] = $v;
            }
        }
        foreach($level_1 as $k1=>$v1){
            $all[] = $v1;
            foreach($new_data as $k2=>$v2){
                if($v2['pt_parent_ikey'] == $v1['ikey']){
                    $v2['pt_name'] = '　　| - '.$v2['pt_name'];
                    $all[] = $v2;
                }
            }
        }
        $m = '';
        // print_r($all);
        foreach($all as $k3=>$v3){
            $m[$v3['ikey']] = $v3['pt_name'];
        }
        return $m;
    }

    /**
     * @ 获取分类,提供问吧首页分类
     * @return array 所有分类
    */
    public static function getTypesForIndex(){

        
        $data = AskbarType::find()->asArray()->all();
        
       
        $level_1 = '';
        foreach($data as $k=>$v){
            if($v['pt_parent_ikey'] == ''){
                $level_1[] = $v;
            }
        }
        //取5个一级分类
        $level_1 = array_slice($level_1, 0, 5); 
        foreach($level_1 as $k1=>&$v1){
            // $all[] = $v1;
            $sub='';
            foreach($data as $k2=>$v2){
                if($v2['pt_parent_ikey'] == $v1['ikey']){
                    $sub[] = $v2;
                }
            }
            $v1['sub'] = $sub;

        }
        
        return $level_1;
    }

    /**
     * @ 获取子分类
     * @param $id 上级分类
     * @return array 所有子分类id
    */
    public static function getSubTypes($id){

        if(empty($id))
            return [];
        $data = AskbarType::find()->where(['pt_parent_ikey'=>$id])->asArray()->all();
        if(is_array($data)){
            foreach($data as $k=>$v){
                $m[] = (int)$v['ikey'];
            }    
        }
        return $m;
    }

    public function getMenuList($params = [])
    {
        $res = self::find()->orderBy('pt_parent_ikey asc')->asArray()->all();
        
        $tempList = [];
        $menu = [];
        $p_id  = 0;
        $begin = 0;
        $menuList = $res;
        $level = [0 => 0];
        $menuTotal = count($menuList);
        while ($begin < $menuTotal) {
            $menuInfo     = $menuList[$begin];
            if ($menuInfo['pt_parent_ikey'] == 0) {
                $menuInfo['last_tree'] = $menuList[$begin]['last_tree'] = [0];
            }

            $p_id         = $menuInfo['ikey'];
            if(empty($menuInfo['pt_parent_ikey'])){
                $menuInfo['pt_parent_ikey'] = 0;
            }
           
            $p_level      = @$level[$menuInfo['pt_parent_ikey']];
            // echo $p_level;
            $level[$p_id] = $menuList[$begin]['level'] = $p_level + 1;
            $isFind       = false;
            $findPosition = 0;
            $findNumber   = 0;
            $begin ++;
            for ($i = $begin; $i < $menuTotal; $i ++) {
                if ($menuList[$i]['pt_parent_ikey'] == $p_id) {
                    $findPosition = $i;
                    $isFind = true;
                    for ($j = $i; ; $j ++) {
                        if (@$menuList[$j]['pt_parent_ikey'] == $p_id) {
                            $findNumber ++;
                        } else {
                            break;
                        }
                    }
                    break;
                }
            }
            if ($isFind) {
                $childrenList = array_splice($menuList, $findPosition, $findNumber);
                for ($i = 0, $j = count($childrenList); $i < $j - 1; $i ++) {
                    $childrenList[$i]['last_tree'] = array_merge($menuInfo['last_tree'], [0]);
                    $childrenList[$i]['is_last'] = 0;
                }
                $childrenList[$j - 1]['last_tree'] = array_merge($menuInfo['last_tree'], [1]);
                $childrenList[$j - 1]['is_last'] = 1;
                array_splice($menuList, $begin, 0, $childrenList);
            }
        }
        // print_r($menuList);
        // exit;

        return $menuList;
    }
    /**
     * @ 获取分类
     * @param $id 上级分类
     * @return array 所有子分类id
    */
    public static function getTypesByPid($pid=0){
        
        $data = AskbarType::find()->where(['pt_parent_ikey'=>$pid])->orderBy('pt_order asc')->asArray()->all();
        // print_r($data);
        if(is_array($data)){
            foreach($data as $k=>&$v){
                $son = self::havSon($v['ikey']);
                if($son){
                    $v['isParent'] = true;
                }else{
                    $v['isParent'] = false;
                }
            }
        }
         return $data; 
    }
    public function havSon($pid){
        if($pid == ''){
            return false;
        }
        $data = AskbarType::find()->where(['pt_parent_ikey'=>$pid])->asArray()->all();
        if(!empty($data)){
            return true;
        }else{
            return false;
        }
    }

}

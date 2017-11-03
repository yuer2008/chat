<?php

namespace app\models;

use Yii;
use yii\mongodb\Query;

/**
 * 
 * 音效分类表
 */
class Effectype extends \yii\mongodb\ActiveRecord
{
    public static function getDb(){
        return \Yii::$app->get('mongodb2');
    }
    /**
     * @inheritdoc
     */
    public static function collectionName()
    {
        return 'effectype';
    }

    public function attributes(){
        return ['_id', 'eid', 'etype', 'eorder'];
    }

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
          
        ];
    }
   

    /**
     * 列表
     * @param 参数
     * @return array
    */
    public static function getList($param=[]){
        $cont = [];
        

        if(!empty($param['keyword'])){
            $cont = [['like','etype',$param['keyword']]];
            
        }
        $data = self::find()->where([])->orderBy('eorder desc');
        return $data;
    }

    /**
     * 删除
     * @return array
    */
    public static function remove($key){
        
        $data = self::findOne($key)->delete();
        
        return $data;
    }

    /**
     * update
     * 
    */
    public static function edit($key){
        $m = self::findOne($key);
        $m->etype = '';
        $m->update();
    }
}

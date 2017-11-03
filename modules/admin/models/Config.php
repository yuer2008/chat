<?php

namespace app\modules\admin\models;

use Yii;
use yii\mongodb\Query;

class Config extends \yii\mongodb\ActiveRecord
{

    public static function getDb(){
        return \Yii::$app->get('mongodb2');
    }
    /**
     * @inheritdoc
     */
    public static function collectionName()
    {
        return 'config';
    }

    public function attributes(){
        return ['_id', 'key', 'value','update_time'];
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
    * 更新配置信息
     * @return 
     */
    public static function edit($key,$value){
        $m = self::findOne(['key'=>$key]);
        $m->value = $value;
        $m->update_time = time();
        return $m->update();
    }

  
    /**
     * 读取配置信息
     * @return array
    */
    public static function getInfo($ikey){
        if(empty($ikey)){
            return;
        }
        $data = self::find()->where(['key'=>"$ikey"])->asArray()->one();
        return $data;
    }
}

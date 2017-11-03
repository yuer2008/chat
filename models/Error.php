<?php
/*
* Time:2016/3/10
* Author:Administrator
*/
namespace app\models;
use Yii;
use yii\db\ActiveRecord;
class Error extends ActiveRecord{
    public static function tableName(){
        return "tb_error";
    }

    public function rules(){
        return [
            [$this->attributes(),"safe"],
        ];
    }
}

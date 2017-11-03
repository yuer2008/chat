<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * RegForm 
 */
class RegForm extends Model
{
    public $snumber;
    public $loginmm;
    public $zcemail;
    public $qqhaoma;
    public $xinming;
    public $password2;
    
    public $smobile;

    public $domain;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            ['loginmm',  'required', 'message'=> '密码必须填写'],
            ['password2','required', 'message'=> '确认密码必须填写'],
            ['password2','compare', 'compareAttribute' => 'loginmm','message'=>'输入密码不一致'],
            //['zcemail',  'unique', 'targetClass' => \app\models\Userinfomongo::className(),'targetAttribute'=>'u_email', 'message' => '此用户名已经被使用。'],

            ['zcemail',  'required', 'message'=> '邮箱必须填写.'],
            ['zcemail',  'email',    'message'=>'邮箱格式错误'],
            //['zcemail',"checkunique"],
            //[['zcemail'],"checkunique"],
            ['loginmm',  'string',   'min' => 6, 'max' => 32, 'tooShort'=>'密码必须大于 6个字符','tooLong'=>'密码小于 20 个字符'],
            
            ['qqhaoma',  'integer','message'=>'QQ号码不正确'],
            ['xinming',  'string',   'min' => 1, 'max' => 12, 'tooShort'=>'姓名大于 1个字符','tooLong'=>'姓名小于 20 个字符'],
            ['smobile',  'integer',  'integerPattern'=>'/^1[3|5|8]\d{9}$/', 'message'=>'手机号码有错误'],
            
        ];
    }


    public function checkunique($attribute,$params){
            $this->addError($attribute,"对不起 ，有错误");
    }

}

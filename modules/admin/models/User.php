<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%user}}".
 *
 * @property integer $ikey
 * @property string $username
 * @property string $userpass
 * @property integer $regtime
 */
class User extends \yii\db\ActiveRecord implements  \yii\web\IdentityInterface
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['regtime'], 'integer'],
            [['username', 'userpass'], 'string', 'max' => 110]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ikey' => 'Ikey',
            'username' => 'Username',
            'userpass' => 'Userpass',
            'regtime' => 'Regtime',
        ];
    }

    public function validatePassword($password){
        return md5($password) ===$this->userpass;
        /*return true;*/
    }
    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        $model = static::findOne($id);
        if($model)
            return $model;
        return null;
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
    }

    /**
     * Finds user by username
     *
     * @param  string      $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        $model = static::find()->where(["username"=>$username])->one();
        if($model)
            return $model;
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->ikey;
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {

    }
}

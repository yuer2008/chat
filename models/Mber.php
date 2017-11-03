<?php

namespace app\models;

class Mber extends \yii\db\ActiveRecord implements \yii\web\IdentityInterface
{
    /**
     * @inheritdoc
    */
   public static function getDb() {
        return \Yii::$app->db;
  }

    public static function tableName()
    {
        return '{{%member}}';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['username', 'password'], 'required'],
            [['id'], 'integer'],
            [['username','email','mobile'], 'string', 'max' => 50],
            [['password'], 'string', 'max' => 32]
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Username',
            'password' => 'Password',
            'email' => 'email',
            'mobile' => 'mobile'
        ];
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::findOne($id);
    }
    public static function findIdentityByAccessToken($token, $type = null){
        return static::findOne(['access_token' => $token]);
    }
    public function getAuthKey(){
        return $this->auth_key;
    }
    // public function getAuthKey(){
    //     return $this->authKey;
    // }
    /**
     * Finds user by username
     *
     * @param  string      $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        $user = static::find()
            ->where(['username' => $username])
            ->asArray()
            ->one();

        if($user){
            return new static($user);
        }
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Validates password
     *
     * @param  string  $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        // return $this->password === $password;
        return $this->password === md5($password);
    }
    public function validateAuthKey($authKey){
        return $this->authKey === $authKey;
    }
}

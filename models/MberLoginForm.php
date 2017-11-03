<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * LoginForm is the model behind the login form.
 */
class MberLoginForm extends Model {
	
	public $username;
	public $password;
	public $rememberMe = true;
	
	private $_user = false;

	// public function scenarios() {
	// 	return [];
	// }
	/**
	 * @return array the validation rules.
	 */
	public function rules() {
		return [
			// username and password are both required
			[['username', 'password'], 'required', 'message' => '* {attribute}不能为空'],
			// rememberMe must be a boolean value
			['rememberMe', 'boolean'],
			['password', 'validatePassword']
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels() {
		return [
			'username' => '帐 号',
			'password' => '密 码',
			'rememberMe' => '记住密码',
		];
	}
	/**
     * Validates the password.验证密码
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if(!$user){
                $this->addError($attribute, '账号不存在');
            }
            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, '错误的帐号或密码');
            }
        }
    }

	/**
	 * Logs in a user using the provided username and password.
	 * @return boolean whether the user is logged in successfully
	 */
	public function login() {
		if ($this->validate()) {
			return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600 * 24 * 30 : 0);
		} else {
			return false;
		}
	}

	/**
	 * Finds user by [[username]]
	 *
	 * @return User|null
	 */
	public function getUser() {
		if ($this->_user === false) {
			$this->_user = \app\models\Mber::findByUsername($this->username);
		}
		return $this->_user;
	}
}

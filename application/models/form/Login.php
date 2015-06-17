<?php namespace App\models\form;
/**
 * Created by PhpStorm.
 * User: guoxiaosong
 * Date: 15/6/12
 * Time: 上午11:40
 */

class Login extends \App\models\base\Model
{
    public $username;
    public $password;
    public $rememberMe;

    private $_user = false;

    public function rules()
    {
        return [
            'username' => 'required|string|between:6,30',
            'password' => 'required|min:6',
            'rememberMe' => 'boolean',
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Incorrect username or password.');
                return false;
            }
        }
        return true;
    }

    /**
     * Logs in a user using the provided username and password.
     *
     * @return boolean whether the user is logged in successfully
     */
    public function login()
    {
        if ($this->validate() && $this->validatePassword('password')) {
            return \Yaf\Registry::get('WebUser')->login($this->getUser(), $this->rememberMe ? 3600 * 24 * 30 : 0);
        } else {
            return false;
        }
    }

    /**
     * Finds user by [[username]]
     *
     * @return \UserModel|null
     */
    public function getUser()
    {
        if ($this->_user === false) {
            $this->_user = \UserModel::findByUsername($this->username);
        }
        return $this->_user;
    }
}
<?php namespace App\models\form;
use App\models\base\Model;

/**
 * Created by PhpStorm.
 * User: guoxiaosong
 * Date: 15/6/10
 * Time: 下午2:34
 */

class Register extends Model
{
    public $username;
    public $password;
    public $repeatPassword;

    public function rules()
    {
        return [
            'username' => 'required|string|between:6,30|unique:users',
            'password' => 'required|min:6',
            'repeatPassword' => 'required|same:password'
        ];
    }

    /**
     * Signs user up.
     *
     * @return \UserModel|null the saved model or null if saving fails
     */
    public function signup()
    {
        if ($this->validate()) {
            $user = new \UserModel();
            $user->username = $this->username;
            $user->setPassword($this->password);
            $user->generateAuthKey();
            $user->save();
            return $user;
        }

        return null;
    }
}
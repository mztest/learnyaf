<?php namespace App\models\form;
use App\models\base\Model;

/**
 * Created by PhpStorm.
 * User: guoxiaosong
 * Date: 15/6/10
 * Time: 下午2:34
 */

class RegisterForm extends Model
{
    public $username;
    public $password;
    public $repeatPassword;
    public function rules()
    {
        return [
            'username' => 'required|string|between:6,30',
            'password' => 'required|min:6',
            'repeatPassword' => 'required|same:password'
        ];
    }
}
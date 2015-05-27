<?php
/**
 * Created by PhpStorm.
 * User: guoxiaosong
 * Date: 15/5/27
 * Time: 上午10:46
 */
class UserModel extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'users';

    protected $guarded = ['id'];
}
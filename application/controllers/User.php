<?php

/**
 * Created by PhpStorm.
 * User: guoxiaosong
 * Date: 15/5/27
 * Time: 上午11:01
 */
//use Illuminate\Database\Capsule\Manager as Capsule;

class UserController extends BaseController
{
    public function indexAction()
    {
        // $users = Capsule::table('users')->get();
        $users = UserModel::all();
        $this->getView()->assign([
            'users' => $users
        ]);
    }
}
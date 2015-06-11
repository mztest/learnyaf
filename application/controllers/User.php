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

    public function viewAction()
    {
        $id = $this->getRequest()->getParam('id');
        echo $id;
        $user = UserModel::find($id);

        print_r($user);
        return false;
    }

}
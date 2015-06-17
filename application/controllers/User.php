<?php

/**
 * Created by PhpStorm.
 * User: guoxiaosong
 * Date: 15/5/27
 * Time: 上午11:01
 */

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
        $user = UserModel::find($id);

        return false;
    }

}
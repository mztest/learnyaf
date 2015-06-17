<?php

/**
 * Created by PhpStorm.
 * User: guoxiaosong
 * Date: 15/6/8
 * Time: 下午3:23
 */
use App\models\form\Register;
use App\models\form\Login;

class AuthController extends BaseController
{
    public function registerAction()
    {
        $form = new Register();

        if ($this->getRequest()->isPost() && $form->load($this->getRequest()->getPost(), '') && $form->validate()) {
            if ($user = $form->signup()) {
            }
        }

        $this->getLayout()->pushArrayVar('breadcrumb', 'register');

        $this->getView()->assign([
            'form' => $form
        ]);

    }

    public function loginAction()
    {
        $form = new Login();
        if ($this->getRequest()->isPost() && $form->load($this->getRequest()->getPost(), '') && $form->login()) {
            // echo 'Login';
            $this->redirect('/');
        }

        $this->getLayout()->pushArrayVar('breadcrumb', 'login');
        $this->getView()->assign([
            'form' => $form
        ]);
    }

    public function logoutAction()
    {
        \Yaf\Registry::get('WebUser')->logout();
        $this->redirect('/');
    }
}
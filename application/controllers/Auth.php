<?php

/**
 * Created by PhpStorm.
 * User: guoxiaosong
 * Date: 15/6/8
 * Time: 下午3:23
 */
use App\models\form\Register;
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

    }
}
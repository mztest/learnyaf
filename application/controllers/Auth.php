<?php

/**
 * Created by PhpStorm.
 * User: guoxiaosong
 * Date: 15/6/8
 * Time: 下午3:23
 */
class AuthController extends BaseController
{
    public function registerAction()
    {
        $form = new \App\models\form\RegisterForm();

        if ($this->getRequest()->isPost() && $form->load($this->getRequest()->getPost(), '') && $form->validate()) {
        }
        var_dump($form->getErrors());
        var_dump($form->attributes());
        $this->getView()->assign([
            'form' => $form
        ]);

    }

    public function loginAction()
    {

    }
}
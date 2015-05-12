<?php

class IndexController extends BaseController
{
    public function indexAction()
    {
        $this->getLayout()->setLayout('home.phtml');
        $this->getView()->assign('name', 'Index');
    }
}
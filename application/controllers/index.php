<?php

class IndexController extends BaseController
{
    public function indexAction()
    {
        $this->getLayout()->setLayout('home.phtml');
        $this->getView()->assign('name', 'Index');
//         $this->getLayout()->title = 'Index page.';
//         Capsule::table('users');
    }
}

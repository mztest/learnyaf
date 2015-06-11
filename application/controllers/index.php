<?php

class IndexController extends BaseController
{
    public function indexAction()
    {
//        $this->getLayout()->setLayout('home.phtml');
        $this->getView()->assign('name', 'I am Index page.');
//         $this->getLayout()->title = 'Index page.';
        var_dump($this->getRequest()->getQuery('abc'));
    }
}

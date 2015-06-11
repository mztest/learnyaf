<?php

class BaseController extends Yaf\Controller_Abstract
{
    private $_layout;
    
    public function init()
    {
        $this->_layout = \Yaf\Registry::get('Layout');
        
        $this->getLayout()->title = $this->getRequest()->getActionName() ."- Learn Yaf";
        $this->getLayout()->breadcrumb = [['label' => 'Home', 'url' => "/"]];
    }
    
    public function getLayout()
    {
        return $this->_layout;
    }
}
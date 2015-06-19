<?php

class BaseController extends Yaf\Controller_Abstract
{
    public $currentMenu;
    private $_layout;
    
    public function init()
    {
        $this->_layout = \Yaf\Registry::get('Layout');
        
        $this->getLayout()->title = $this->getRequest()->getControllerName() .'-'. $this->getRequest()->getActionName
    () ."-
        Learn Yaf";
        $this->getLayout()->breadcrumb = [['label' => 'Home', 'url' => "/"]];
    }
    
    public function getLayout()
    {
        return $this->_layout;
    }
}
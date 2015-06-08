<?php
/**
 * Created by PhpStorm.
 * User: guoxiaosong
 * Date: 15/6/3
 * Time: 下午2:58
 */

class DummyPlugin extends \Yaf\Plugin_Abstract {
    public function routerStartup(Yaf\Request_Abstract $request, Yaf\Response_Abstract $response) {
        /* 在路由之前执行,这个钩子里，你可以做url重写等功能 */
        var_dump("routerStartup");
    }
    public function routerShutdown(Yaf\Request_Abstract $request, Yaf\Response_Abstract $response) {
        /* 路由完成后，在这个钩子里，你可以做登陆检测等功能*/
        var_dump($request);
        var_dump($response);
        var_dump("routerShutdown");
    }
    public function dispatchLoopStartup(Yaf\Request_Abstract $request, Yaf\Response_Abstract $response) {
        var_dump("dispatchLoopStartup");
    }
    public function preDispatch(Yaf\Request_Abstract $request, Yaf\Response_Abstract $response) {
        var_dump("preDispatch");
    }
    public function postDispatch(Yaf\Request_Abstract $request, Yaf\Response_Abstract $response) {
        var_dump("postDispatch");
    }
    public function dispatchLoopShutdown(Yaf\Request_Abstract $request, Yaf\Response_Abstract $response) {
        /* final hoook
           in this hook user can do loging or implement layout */
        var_dump("dispatchLoopShutdown");
    }
}
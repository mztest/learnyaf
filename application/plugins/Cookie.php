<?php
/**
 * Created by PhpStorm.
 * User: guoxiaosong
 * Date: 15/6/17
 * Time: 下午5:14
 */

class CookiePlugin extends \Yaf\Plugin_Abstract
{
    /**
     *
     * @param \Yaf\Request_Abstract $request
     * @param \Yaf\Response_Abstract $response
     */
    public function postDispatch(\Yaf\Request_Abstract $request, \Yaf\Response_Abstract $response)
    {
        Yaf\Registry::get('Response')->sendCookies();
    }
}
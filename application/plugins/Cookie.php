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
    public function dispatchLoopShutdown(\Yaf\Request_Abstract $request, \Yaf\Response_Abstract $response)
    {
        $cookies = Yaf\Registry::get('Response')->getCookies();
        $validationKey = \Yaf\Registry::get('config')->get('application.cookieValidationKey');
        foreach($cookies as $cookie) {
            $value = $cookie->value;
            if ($cookie->expire != 1  && isset($validationKey)) {
                $value = \Yaf\Registry::get('Security')->hashData(serialize($value), $validationKey);
            }
            setcookie($cookie->name, $value, $cookie->expire, $cookie->path, $cookie->domain, $cookie->secure, $cookie->httpOnly);
        }
        $cookies->removeAll();
    }
}
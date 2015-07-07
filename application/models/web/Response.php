<?php namespace App\models\web;
/**
 * Created by PhpStorm.
 * User: guoxiaosong
 * Date: 15/6/17
 * Time: 下午2:03
 */

class Response extends \App\models\base\Object
{
    private $_cookies;

    /**
     * Returns the cookie collection.
     * Through the returned cookie collection, you add or remove cookies as follows,
     *
     * ~~~
     * // add a cookie
     * $response->cookies->add(new Cookie([
     *     'name' => $name,
     *     'value' => $value,
     * ]);
     *
     * // remove a cookie
     * $response->cookies->remove('name');
     * // alternatively
     * unset($response->cookies['name']);
     * ~~~
     *
     * @return CookieCollection the cookie collection.
     */
    public function getCookies()
    {
        if ($this->_cookies === null) {
            $this->_cookies = new CookieCollection;
        }
        return $this->_cookies;
    }

    public function sendCookies()
    {
        $cookies = $this->getCookies();
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
<?php namespace App\models\web;
/**
 * Created by PhpStorm.
 * User: guoxiaosong
 * Date: 15/6/17
 * Time: 下午1:52
 */

use App\models\base\Exception;

class Request extends \App\models\base\Object
{
    /**
     * @var boolean whether cookies should be validated to ensure they are not tampered. Defaults to true.
     */
    public $enableCookieValidation = true;
    /**
     * @var string a secret key used for cookie validation. This property must be set if [[enableCookieValidation]] is true.
     */
    public $cookieValidationKey;

    private $_cookies;

    /**
     * Returns the cookie collection.
     * Through the returned cookie collection, you may access a cookie using the following syntax:
     *
     * ~~~
     * $cookie = $request->cookies['name']
     * if ($cookie !== null) {
     *     $value = $cookie->value;
     * }
     *
     * // alternatively
     * $value = $request->cookies->getValue('name');
     * ~~~
     *
     * @return CookieCollection the cookie collection.
     */
    public function getCookies()
    {
        if ($this->_cookies === null) {
            $this->_cookies = new CookieCollection($this->loadCookies(), [
                'readOnly' => true,
            ]);
        }

        return $this->_cookies;
    }

    /**
     * Converts `$_COOKIE` into an array of [[Cookie]].
     * @return array the cookies obtained from request
     * @throws Exception if [[cookieValidationKey]] is not set when [[enableCookieValidation]] is true
     */
    protected function loadCookies()
    {
        $cookies = [];
        if ($this->enableCookieValidation) {
            if ($this->cookieValidationKey == '') {
                throw new Exception(get_class($this) . '::cookieValidationKey must be configured with a secret key.');
            }
            foreach ($_COOKIE as $name => $value) {
                if (is_string($value) && ($value = \Yaf\Registry::get('Security')->validateData($value,
                        $this->cookieValidationKey)) !== false) {
                    $cookies[$name] = new Cookie([
                        'name' => $name,
                        'value' => @unserialize($value),
                        'expire'=> null
                    ]);
                }
            }
        } else {
            foreach ($_COOKIE as $name => $value) {
                $cookies[$name] = new Cookie([
                    'name' => $name,
                    'value' => $value,
                    'expire'=> null
                ]);
            }
        }

        return $cookies;
    }

    private $_hostInfo;

    /**
     * Returns the schema and host part of the current request URL.
     * The returned URL does not have an ending slash.
     * By default this is determined based on the user request information.
     * You may explicitly specify it by setting the [[setHostInfo()|hostInfo]] property.
     * @return string schema and hostname part (with port number if needed) of the request URL (e.g. `http://www.yiiframework.com`)
     * @see setHostInfo()
     */
    public function getHostInfo()
    {
        if ($this->_hostInfo === null) {
            $secure = $this->getIsSecureConnection();
            $http = $secure ? 'https' : 'http';
            if (isset($_SERVER['HTTP_HOST'])) {
                $this->_hostInfo = $http . '://' . $_SERVER['HTTP_HOST'];
            } else {
                $this->_hostInfo = $http . '://' . $_SERVER['SERVER_NAME'];
                $port = $secure ? $this->getSecurePort() : $this->getPort();
                if (($port !== 80 && !$secure) || ($port !== 443 && $secure)) {
                    $this->_hostInfo .= ':' . $port;
                }
            }
        }

        return $this->_hostInfo;
    }

    /**
     * Sets the schema and host part of the application URL.
     * This setter is provided in case the schema and hostname cannot be determined
     * on certain Web servers.
     * @param string $value the schema and host part of the application URL. The trailing slashes will be removed.
     */
    public function setHostInfo($value)
    {
        $this->_hostInfo = rtrim($value, '/');
    }

    /**
     * Return if the request is sent via secure channel (https).
     * @return boolean if the request is sent via secure channel (https)
     */
    public function getIsSecureConnection()
    {
        return isset($_SERVER['HTTPS']) && (strcasecmp($_SERVER['HTTPS'], 'on') === 0 || $_SERVER['HTTPS'] == 1)
        || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strcasecmp($_SERVER['HTTP_X_FORWARDED_PROTO'], 'https') === 0;
    }

    private $_port;

    /**
     * Returns the port to use for insecure requests.
     * Defaults to 80, or the port specified by the server if the current
     * request is insecure.
     * @return integer port number for insecure requests.
     * @see setPort()
     */
    public function getPort()
    {
        if ($this->_port === null) {
            $this->_port = !$this->getIsSecureConnection() && isset($_SERVER['SERVER_PORT']) ? (int) $_SERVER['SERVER_PORT'] : 80;
        }

        return $this->_port;
    }

    /**
     * Sets the port to use for insecure requests.
     * This setter is provided in case a custom port is necessary for certain
     * server configurations.
     * @param integer $value port number.
     */
    public function setPort($value)
    {
        if ($value != $this->_port) {
            $this->_port = (int) $value;
            $this->_hostInfo = null;
        }
    }

    private $_securePort;

    /**
     * Returns the port to use for secure requests.
     * Defaults to 443, or the port specified by the server if the current
     * request is secure.
     * @return integer port number for secure requests.
     * @see setSecurePort()
     */
    public function getSecurePort()
    {
        if ($this->_securePort === null) {
            $this->_securePort = $this->getIsSecureConnection() && isset($_SERVER['SERVER_PORT']) ? (int) $_SERVER['SERVER_PORT'] : 443;
        }

        return $this->_securePort;
    }

    /**
     * Sets the port to use for secure requests.
     * This setter is provided in case a custom port is necessary for certain
     * server configurations.
     * @param integer $value port number.
     */
    public function setSecurePort($value)
    {
        if ($value != $this->_securePort) {
            $this->_securePort = (int) $value;
            $this->_hostInfo = null;
        }
    }

    private $_route;

    public function getRoute()
    {
        if ($this->_route === null) {
            $this->_route = $this->getControllerName() . '/'. $this->getActionName();
            if ('index' != $this->getModuleName()) {
                $this->_route = $this->getModuleName() .'/'. $this->_route;
            }
        }

        return $this->_route;
    }

    private $_yafRequest;

    /**
     * @return mixed
     */
    public function getYafRequest()
    {
        if ($this->_yafRequest === null) {
            $this->_yafRequest = \Yaf\Application::app()->getDispatcher()->getRequest();
        }
        return $this->_yafRequest;
    }

    private $_moduleName;

    /**
     * @return string
     */
    public function getModuleName()
    {
        if ($this->_moduleName === null) {
            $this->_moduleName = strtolower($this->getYafRequest()->getModuleName());
        }
        return $this->_moduleName;
    }

    private $_controllerName;

    /**
     * @return string
     */
    public function getControllerName()
    {
        if ($this->_controllerName === null) {
            $this->_controllerName = strtolower($this->getYafRequest()->getControllerName());
        }
        return $this->_controllerName;
    }

    private $_actionName;

    /**
     * @return string
     */
    public function getActionName()
    {
        if ($this->_actionName === null) {
            $this->_actionName = strtolower($this->getYafRequest()->getActionName());
        }
        return $this->_actionName;
    }

    private $_homeUrl;

    /**
     * @return string
     */
    public function getHomeUrl()
    {
        if ($this->_homeUrl === null) {
            $defaultModule = \Yaf\Registry::get('config').get('application.dispatcher.defaultModule');
            $defaultController = \Yaf\Registry::get('config').get('application.dispatcher.defaultController');
            $defaultAction = \Yaf\Registry::get('config').get('application.dispatcher.defaultAction');

            $this->_homeUrl = $defaultController .'/'. $defaultAction;
            if ('index' != $defaultModule) {
                $this->_homeUrl = $defaultModule .'/'. $this->_homeUrl;
            }
        }
        return $this->_homeUrl;
    }
}
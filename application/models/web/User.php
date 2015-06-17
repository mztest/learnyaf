<?php namespace App\models\web;
/**
 * Created by PhpStorm.
 * User: guoxiaosong
 * Date: 15/6/12
 * Time: 下午3:16
 */

use App\models\base\Exception;
use App\models\base\Object;

class User extends Object
{
    /**
     * @var string the class name of the [[identity]] object.
     */
    public $identityClass;
    /**
     * @var boolean whether to enable cookie-based login. Defaults to false.
     * Note that this property will be ignored if [[enableSession]] is false.
     */
    public $enableAutoLogin = false;
    /**
     * @var boolean whether to use session to persist authentication status across multiple requests.
     * You set this property to be false if your application is stateless, which is often the case
     * for RESTful APIs.
     */
    public $enableSession = true;
    /**
     * @var integer the number of seconds in which the user will be logged out automatically if he
     * remains inactive. If this property is not set, the user will be logged out after
     * the current session expires (c.f. [[Session::timeout]]).
     * Note that this will not work if [[enableAutoLogin]] is true.
     */
    public $authTimeout;
    /**
     * @var integer the number of seconds in which the user will be logged out automatically
     * regardless of activity.
     * Note that this will not work if [[enableAutoLogin]] is true.
     */
    public $absoluteAuthTimeout;
    /**
     * @var array the configuration of the identity cookie. This property is used only when [[enableAutoLogin]] is true.
     * @see Cookie
     */
    public $identityCookie = ['name' => '_identity', 'httpOnly' => true];

    private $_identity = false;

    /**
     * @var boolean whether to automatically renew the identity cookie each time a page is requested.
     * This property is effective only when [[enableAutoLogin]] is true.
     * When this is false, the identity cookie will expire after the specified duration since the user
     * is initially logged in. When this is true, the identity cookie will expire after the specified duration
     * since the user visits the site the last time.
     * @see enableAutoLogin
     */
    public $autoRenewCookie = true;
    /**
     * @var string the session variable name used to store the value of [[id]].
     */
    public $idParam = '__id';
    /**
     * @var string the session variable name used to store the value of expiration timestamp of the authenticated state.
     * This is used when [[authTimeout]] is set.
     */
    public $authTimeoutParam = '__expire';
    /**
     * @var string the session variable name used to store the value of absolute expiration timestamp of the authenticated state.
     * This is used when [[absoluteAuthTimeout]] is set.
     */
    public $absoluteAuthTimeoutParam = '__absoluteExpire';
    /**
     * @var string the session variable name used to store the value of [[returnUrl]].
     */
    public $returnUrlParam = '__returnUrl';

    /**
     * Returns the identity object associated with the currently logged-in user.
     * When [[enableSession]] is true, this method may attempt to read the user's authentication data
     * stored in session and reconstruct the corresponding identity object, if it has not done so before.
     * @param boolean $autoRenew whether to automatically renew authentication status if it has not been done so before.
     * This is only useful when [[enableSession]] is true.
     * @return IdentityInterface|null the identity object associated with the currently logged-in user.
     * `null` is returned if the user is not logged in (not authenticated).
     * @see login()
     * @see logout()
     */
    public function getIdentity($autoRenew = true)
    {
        if ($this->_identity === false) {
            if ($this->enableSession && $autoRenew) {
                $this->_identity = null;
                $this->renewAuthStatus();
            } else {
                return null;
            }
        }

        return $this->_identity;
    }

    /**
     * Sets the user identity object.
     *
     * Note that this method does not deal with session or cookie. You should usually use [[switchIdentity()]]
     * to change the identity of the current user.
     *
     * @param IdentityInterface|null $identity the identity object associated with the currently logged user.
     * If null, it means the current user will be a guest without any associated identity.
     * @throws Exception if `$identity` object does not implement [[IdentityInterface]].
     */
    public function setIdentity($identity)
    {
        if ($identity instanceof IdentityInterface) {
            $this->_identity = $identity;
        } elseif ($identity === null) {
            $this->_identity = null;
        } else {
            throw new Exception('The identity object must implement IdentityInterface.');
        }
    }

    /**
     * Logs in a user.
     *
     * After logging in a user, you may obtain the user's identity information from the [[identity]] property.
     * If [[enableSession]] is true, you may even get the identity information in the next requests without
     * calling this method again.
     *
     * The login status is maintained according to the `$duration` parameter:
     *
     * - `$duration == 0`: the identity information will be stored in session and will be available
     *   via [[identity]] as long as the session remains active.
     * - `$duration > 0`: the identity information will be stored in session. If [[enableAutoLogin]] is true,
     *   it will also be stored in a cookie which will expire in `$duration` seconds. As long as
     *   the cookie remains valid or the session is active, you may obtain the user identity information
     *   via [[identity]].
     *
     * Note that if [[enableSession]] is false, the `$duration` parameter will be ignored as it is meaningless
     * in this case.
     *
     * @param IdentityInterface $identity the user identity (which should already be authenticated)
     * @param integer $duration number of seconds that the user can remain in logged-in status.
     * Defaults to 0, meaning login till the user closes the browser or the session is manually destroyed.
     * If greater than 0 and [[enableAutoLogin]] is true, cookie-based login will be supported.
     * Note that if [[enableSession]] is false, this parameter will be ignored.
     * @return boolean whether the user is logged in
     */
    public function login(IdentityInterface $identity, $duration = 0)
    {
        if ($this->beforeLogin($identity, false, $duration)) {
            $this->switchIdentity($identity, $duration);
            $this->afterLogin($identity, false, $duration);
        }

        return !$this->getIsGuest();
    }

    /**
     * Logs out the current user.
     * This will remove authentication-related session data.
     * If `$destroySession` is true, all session data will be removed.
     * @param boolean $destroySession whether to destroy the whole session. Defaults to true.
     * This parameter is ignored if [[enableSession]] is false.
     * @return boolean whether the user is logged out
     */
    public function logout($destroySession = true)
    {
        $identity = $this->getIdentity();
        if ($identity !== null && $this->beforeLogout($identity)) {
            $this->switchIdentity(null);
            if ($destroySession && $this->enableSession) {
                @session_unset();
                @session_destroy();
            }
            $this->afterLogout($identity);
        }

        return $this->getIsGuest();
    }

    /**
     * Returns a value indicating whether the user is a guest (not authenticated).
     * @return boolean whether the current user is a guest.
     * @see getIdentity()
     */
    public function getIsGuest()
    {
        return $this->getIdentity() === null;
    }

    /**
     * This method is called before logging in a user.
     * The default implementation will trigger the [[EVENT_BEFORE_LOGIN]] event.
     * If you override this method, make sure you call the parent implementation
     * so that the event is triggered.
     * @param IdentityInterface $identity the user identity information
     * @param boolean $cookieBased whether the login is cookie-based
     * @param integer $duration number of seconds that the user can remain in logged-in status.
     * If 0, it means login till the user closes the browser or the session is manually destroyed.
     * @return boolean whether the user should continue to be logged in
     */
    protected function beforeLogin($identity, $cookieBased, $duration)
    {
        /*$event = new UserEvent([
            'identity' => $identity,
            'cookieBased' => $cookieBased,
            'duration' => $duration,
        ]);
        $this->trigger(self::EVENT_BEFORE_LOGIN, $event);

        return $event->isValid;*/
        return true;
    }

    /**
     * This method is called after the user is successfully logged in.
     * The default implementation will trigger the [[EVENT_AFTER_LOGIN]] event.
     * If you override this method, make sure you call the parent implementation
     * so that the event is triggered.
     * @param IdentityInterface $identity the user identity information
     * @param boolean $cookieBased whether the login is cookie-based
     * @param integer $duration number of seconds that the user can remain in logged-in status.
     * If 0, it means login till the user closes the browser or the session is manually destroyed.
     */
    protected function afterLogin($identity, $cookieBased, $duration)
    {
        /*$this->trigger(self::EVENT_AFTER_LOGIN, new UserEvent([
            'identity' => $identity,
            'cookieBased' => $cookieBased,
            'duration' => $duration,
        ]));*/
    }

    /**
     * This method is invoked when calling [[logout()]] to log out a user.
     * The default implementation will trigger the [[EVENT_BEFORE_LOGOUT]] event.
     * If you override this method, make sure you call the parent implementation
     * so that the event is triggered.
     * @param IdentityInterface $identity the user identity information
     * @return boolean whether the user should continue to be logged out
     */
    protected function beforeLogout($identity)
    {
        /*$event = new UserEvent([
            'identity' => $identity,
        ]);
        $this->trigger(self::EVENT_BEFORE_LOGOUT, $event);

        return $event->isValid;*/
        return true;
    }

    /**
     * This method is invoked right after a user is logged out via [[logout()]].
     * The default implementation will trigger the [[EVENT_AFTER_LOGOUT]] event.
     * If you override this method, make sure you call the parent implementation
     * so that the event is triggered.
     * @param IdentityInterface $identity the user identity information
     */
    protected function afterLogout($identity)
    {
        /*$this->trigger(self::EVENT_AFTER_LOGOUT, new UserEvent([
            'identity' => $identity,
        ]));*/
    }

    /**
     * Sends an identity cookie.
     * This method is used when [[enableAutoLogin]] is true.
     * It saves [[id]], [[IdentityInterface::getAuthKey()|auth key]], and the duration of cookie-based login
     * information in the cookie.
     * @param IdentityInterface $identity
     * @param integer $duration number of seconds that the user can remain in logged-in status.
     * @see loginByCookie()
     */
    protected function sendIdentityCookie($identity, $duration)
    {
        $cookie = new Cookie($this->identityCookie);
        $cookie->value = json_encode([
            $identity->getId(),
            $identity->getAuthKey(),
            $duration,
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $cookie->expire = time() + $duration;
        \Yaf\Registry::get('CookieCollection')->add($cookie);
    }

    /**
     * Switches to a new identity for the current user.
     *
     * When [[enableSession]] is true, this method may use session and/or cookie to store the user identity information,
     * according to the value of `$duration`. Please refer to [[login()]] for more details.
     *
     * This method is mainly called by [[login()]], [[logout()]] and [[loginByCookie()]]
     * when the current user needs to be associated with the corresponding identity information.
     *
     * @param IdentityInterface|null $identity the identity information to be associated with the current user.
     * If null, it means switching the current user to be a guest.
     * @param integer $duration number of seconds that the user can remain in logged-in status.
     * This parameter is used only when `$identity` is not null.
     */
    public function switchIdentity($identity, $duration = 0)
    {
        $this->setIdentity($identity);

        if (!$this->enableSession) {
            return;
        }

        $session = \Yaf\Session::getInstance();
        $session->del($this->idParam);
        $session->del($this->authTimeoutParam);

        if ($identity) {
            $session->set($this->idParam, $identity->getId());
            if ($this->authTimeout !== null) {
                $session->set($this->authTimeoutParam, time() + $this->authTimeout);
            }
            if ($this->absoluteAuthTimeout !== null) {
                $session->set($this->absoluteAuthTimeoutParam, time() + $this->absoluteAuthTimeout);
            }
            if ($duration > 0 && $this->enableAutoLogin) {
                $this->sendIdentityCookie($identity, $duration);
            }
        } elseif ($this->enableAutoLogin) {
            \Yaf\Registry::get('CookieCollection')->remove(new Cookie($this->identityCookie));
        }
    }

    /**
     * Updates the authentication status using the information from session and cookie.
     *
     * This method will try to determine the user identity using the [[idParam]] session variable.
     *
     * If [[authTimeout]] is set, this method will refresh the timer.
     *
     * If the user identity cannot be determined by session, this method will try to [[loginByCookie()|login by cookie]]
     * if [[enableAutoLogin]] is true.
     */
    protected function renewAuthStatus()
    {
        $session = \Yaf\Session::getInstance();
        $id = $session->get($this->idParam);

        if ($id === null) {
            $identity = null;
        } else {
            /* @var $class IdentityInterface */
            $class = $this->identityClass;
            echo $class;
            $identity = $class::findIdentity($id);
        }

        $this->setIdentity($identity);

        if ($identity !== null && ($this->authTimeout !== null || $this->absoluteAuthTimeout !== null)) {
            $expire = $this->authTimeout !== null ? $session->get($this->authTimeoutParam) : null;
            $expireAbsolute = $this->absoluteAuthTimeout !== null ? $session->get($this->absoluteAuthTimeoutParam) : null;
            if ($expire !== null && $expire < time() || $expireAbsolute !== null && $expireAbsolute < time()) {
                $this->logout(false);
            } elseif ($this->authTimeout !== null) {
                $session->set($this->authTimeoutParam, time() + $this->authTimeout);
            }
        }

        if ($this->enableAutoLogin) {
            if ($this->getIsGuest()) {
                $this->loginByCookie();
            } elseif ($this->autoRenewCookie) {
                $this->renewIdentityCookie();
            }
        }
    }

    /**
     * Logs in a user by cookie.
     *
     * This method attempts to log in a user using the ID and authKey information
     * provided by the [[identityCookie|identity cookie]].
     */
    protected function loginByCookie()
    {
        $value = \Yaf\Registry::get('CookieCollection')->getValue($this->identityCookie['name']);
        if ($value === null) {
            return;
        }

        $data = json_decode($value, true);
        if (count($data) !== 3 || !isset($data[0], $data[1], $data[2])) {
            return;
        }

        list ($id, $authKey, $duration) = $data;
        /* @var $class IdentityInterface */
        $class = $this->identityClass;
        $identity = $class::findIdentity($id);
        if ($identity === null) {
            return;
        } elseif (!$identity instanceof IdentityInterface) {
            throw new Exception("$class::findIdentity() must return an object implementing IdentityInterface.");
        }

        if ($identity->validateAuthKey($authKey)) {
            if ($this->beforeLogin($identity, true, $duration)) {
                $this->switchIdentity($identity, $this->autoRenewCookie ? $duration : 0);
                $this->afterLogin($identity, true, $duration);
            }
        } else {
        }
    }

    /**
     * Renews the identity cookie.
     * This method will set the expiration time of the identity cookie to be the current time
     * plus the originally specified cookie duration.
     */
    protected function renewIdentityCookie()
    {
        $name = $this->identityCookie['name'];
        $value = \Yaf\Registry::get('CookieCollection')->getValue($name);
        if ($value !== null) {
            $data = json_decode($value, true);
            if (is_array($data) && isset($data[2])) {
                $cookie = new Cookie($this->identityCookie);
                $cookie->value = $value;
                $cookie->expire = time() + (int) $data[2];
                \Yaf\Registry::get('CookieCollection')->add($cookie);
            }
        }
    }

    /**
     * Returns the URL that the browser should be redirected to after successful login.
     *
     * This method reads the return URL from the session. It is usually used by the login action which
     * may call this method to redirect the browser to where it goes after successful authentication.
     *
     * @param string|array $defaultUrl the default return URL in case it was not set previously.
     * If this is null and the return URL was not set previously, [[Application::homeUrl]] will be redirected to.
     * Please refer to [[setReturnUrl()]] on accepted format of the URL.
     * @return string the URL that the user should be redirected to after login.
     * @see loginRequired()
     */
    public function getReturnUrl($defaultUrl = null)
    {
        $session = \Yaf\Session::getInstance();
        $url = $session->get($this->returnUrlParam, $defaultUrl);
        if (is_array($url)) {
            if (isset($url[0])) {
                return Yii::$app->getUrlManager()->createUrl($url);
            } else {
                $url = null;
            }
        }

        return $url === null ? '/' : $url;
    }

    /**
     * Remembers the URL in the session so that it can be retrieved back later by [[getReturnUrl()]].
     * @param string|array $url the URL that the user should be redirected to after login.
     * If an array is given, [[UrlManager::createUrl()]] will be called to create the corresponding URL.
     * The first element of the array should be the route, and the rest of
     * the name-value pairs are GET parameters used to construct the URL. For example,
     *
     * ~~~
     * ['admin/index', 'ref' => 1]
     * ~~~
     */
    public function setReturnUrl($url)
    {
        \Yaf\Session::getInstance()->set($this->returnUrlParam, $url);
    }
}
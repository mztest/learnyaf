<?php
/**
 * Created by PhpStorm.
 * User: guoxiaosong
 * Date: 15/6/18
 * Time: 下午3:02
 */
namespace App\helpers;

use App\models\base\Exception;

class UrlHelper
{
    /**
     * Creates a URL for the given route.
     *
     * This method will create a URL.
     *
     * Below are some examples of using this method:
     *
     * ```php
     * // /index?r=site/index
     * echo Url::toRoute('site/index');
     *
     * // /index?r=site/index&src=ref1#name
     * echo Url::toRoute(['site/index', 'src' => 'ref1', '#' => 'name']);
     *
     * // http://www.example.com/index.php?r=site/index
     * echo Url::toRoute('site/index', true);
     *
     * // https://www.example.com/index.php?r=site/index
     * echo Url::toRoute('site/index', 'https');
     * ```
     *
     * @param string|array $route use a string to represent a route (e.g. `index`, `site/index`),
     * or an array to represent a route with query parameters (e.g. `['site/index', 'param1' => 'value1']`).
     * @param boolean|string $scheme the URI scheme to use in the generated URL:
     *
     * - `false` (default): generating a relative URL.
     * - `true`: returning an absolute base URL whose scheme is the same as that in [[\yii\web\UrlManager::hostInfo]].
     * - string: generating an absolute URL with the specified scheme (either `http` or `https`).
     *
     * @return string the generated URL
     * @throws InvalidParamException a relative route is given while there is no active controller
     */
    public static function toRoute($route, $scheme = false)
    {
        $route = (array) $route;
        $route[0] = static::normalizeRoute($route[0]);

        if ($scheme) {
            return static::createAbsoluteUrl($route, is_string($scheme) ? $scheme : null);
        } else {
            return static::createUrl($route);
        }
    }

    /**
     * Normalizes route and makes it suitable for UrlManager. Absolute routes are staying as is
     * while relative routes are converted to absolute ones.
     *
     * A relative route is a route without a leading slash, such as "view", "post/view".
     *
     * - If the route is an empty string, the current [[\yii\web\Controller::route|route]] will be used;
     * - If the route contains no slashes at all, it is considered to be an action ID
     *   of the current controller and will be prepended with [[\yii\web\Controller::uniqueId]];
     * - If the route has no leading slash, it is considered to be a route relative
     *   to the current module and will be prepended with the module's uniqueId.
     *
     * @param string $route the route. This can be either an absolute route or a relative route.
     * @return string normalized route suitable for UrlManager
     * @throws Exception a relative route is given while there is no active controller
     */
    protected static function normalizeRoute($route)
    {
        $route = (string) $route;
        if (strncmp($route, '/', 1) === 0) {
            // absolute route
            return ltrim($route, '/');
        }

        $yafRequest = \Yaf\Application::app()->getDispatcher()->getRequest();
        // relative route
        if ($yafRequest === null) {
            throw new Exception("Unable to resolve the relative route: $route. No active controller is available.");
        }
        $moduleName = strtolower($yafRequest->getModuleName());
        $controllerName = strtolower($yafRequest->getControllerName());

        if (strpos($route, '/') === false) {
            // empty or an action ID
            if ($route === '') {
                return $controllerName;
            } else if ('index' == $moduleName) {
                return $controllerName . '/' . $route;
            } else {
                return $moduleName .'/'. $controllerName . '/' . $route;
            }
        } else {
            // relative to module
            if ('index' == $moduleName) {
                return ltrim($route . '/');
            } else {
                return ltrim($moduleName . '/' . $route, '/');
            }
        }
    }

    /**
     * Creates a URL using the given route and query parameters.
     *
     * You may specify the route as a string, e.g., `site/index`. You may also use an array
     * if you want to specify additional query parameters for the URL being created. The
     * array format must be:
     *
     * ```php
     * // generates: /index.php?r=site/index&param1=value1&param2=value2
     * ['site/index', 'param1' => 'value1', 'param2' => 'value2']
     * ```
     *
     * If you want to create a URL with an anchor, you can use the array format with a `#` parameter.
     * For example,
     *
     * ```php
     * // generates: /index.php?r=site/index&param1=value1#name
     * ['site/index', 'param1' => 'value1', '#' => 'name']
     * ```
     *
     * The URL created is a relative one. Use [[createAbsoluteUrl()]] to create an absolute URL.
     *
     * Note that unlike [[\yii\helpers\Url::toRoute()]], this method always treats the given route
     * as an absolute route.
     *
     * @param string|array $params use a string to represent a route (e.g. `site/index`),
     * or an array to represent a route with query parameters (e.g. `['site/index', 'param1' => 'value1']`).
     * @return string the created URL
     */
    protected static function createUrl($params)
    {
        $params = (array) $params;
        $anchor = isset($params['#']) ? '#' . $params['#'] : '';
        unset($params['#']);

        $route = trim($params[0], '/');
        unset($params[0]);

        if (!empty($params)) {
            if (strpos($route, 'index') === 0 && ($query = http_build_query($params)) !== '') {
                $route .= '?' . $query;
            } else {
                foreach($params as $key=>$val) {
                    $route .= "/{$key}/".urlencode($val);
                }
            }
        }
        return "/{$route}{$anchor}";
    }

    /**
     * Creates an absolute URL using the given route and query parameters.
     *
     * This method prepends the URL created by [[createUrl()]] with the [[hostInfo]].
     *
     * Note that unlike [[\yii\helpers\Url::toRoute()]], this method always treats the given route
     * as an absolute route.
     *
     * @param string|array $params use a string to represent a route (e.g. `site/index`),
     * or an array to represent a route with query parameters (e.g. `['site/index', 'param1' => 'value1']`).
     * @param string $scheme the scheme to use for the url (either `http` or `https`). If not specified
     * the scheme of the current request will be used.
     * @return string the created URL
     * @see createUrl()
     */
    protected static function createAbsoluteUrl($params, $scheme = null)
    {
        $params = (array) $params;
        $url = static::createUrl($params);
        if (strpos($url, '://') === false) {
            $url = \Yaf\Registry::get('Request')->getHostInfo() . $url;
        }
        if (is_string($scheme) && ($pos = strpos($url, '://')) !== false) {
            $url = $scheme . substr($url, $pos);
        }

        return $url;
    }


}
<?php
/**
 * Plugin templates
 *
 */
use \Yaf\Request_Abstract as Request;
use \Yaf\Response_Abstract as Response;

class LayoutPlugin extends \Yaf\Plugin_Abstract
{
    /**
     * @var string
     */
    private $_layoutDir;
    /**
     * @var string
     */
    private $_layoutFile;
    /**
     * @var array
     */
    private $_layoutVars = array();
    /**
     * Construtor
     *
     * @param string $layoutFile
     * @param string $layoutDir
     */
    public function __construct($layoutDir, $layoutFile=null)
    {
        $this->_layoutFile = $layoutFile ? $layoutFile : 'default.phtml';
        $this->_layoutDir  = $layoutDir;
    }
    /**
     *
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        $this->_layoutVars[$name] = $value;
    }

    /**
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->_layoutVars[$name];
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function pushArrayVar($name, $value)
    {
        if (is_array($this->_layoutVars[$name])) {
            $this->_layoutVars[$name][] = $value;
        }
    }

    /**
     * @param $layoutFile
     */
    public function setLayout( $layoutFile )
    {
        $this->_layoutFile = $layoutFile;
    }
    
    public function getLayout()
    {
        return $this->_layoutFile;
    }
    /**
     *
     * @param \Yaf\Request_Abstract $request
     * @param \Yaf\Response_Abstract $response
     */
    public function routerStartup(Request $request, Response $response)
    {
    }
    /**
     *
     * @param \Yaf\Request_Abstract $request
     * @param \Yaf\Response_Abstract $response
     */
    public function routerShutdown(Request $request, Response $response)
    {
    }
    /**
     *
     * @param \Yaf\Request_Abstract $request
     * @param \Yaf\Response_Abstract $response
     */
    public function dispatchLoopStartup(Request $request, Response $response)
    {
    }
    /**
     *
     * @param \Yaf\Request_Abstract $request
     * @param \Yaf\Response_Abstract $response
     */
    public function preDispatch(Request $request, Response $response)
    {
    }

    /**
     *
     * @param \Yaf\Request_Abstract $request
     * @param \Yaf\Response_Abstract $response
     */
    public function postDispatch(Request $request, Response $response)
    {
        /* get the body of the response */
        $body = $response->getBody();
        if (!$this->_layoutFile || !$body) {
            return;
        }
        /*clear existing response*/
        $response->clearBody();
        /* wrap it in the layout */
        $layout = new \Yaf\View\Simple($this->_layoutDir);
        $layout->content = $body;
        $layout->assign('layout', $this->_layoutVars);
        /* set the response to use the wrapped version of the content */
        $response->setBody($layout->render($this->_layoutFile));
    }
    /**
     *
     * @param \Yaf\Request_Abstract $request
     * @param \Yaf\Response_Abstract $response
     */
    public function dispatchLoopShutdown(Request $request, Response $response)
    {
//        var_dump('dispatchLoopShutdown');
    }


}
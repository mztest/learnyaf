<?php
/**
 * @name Bootstrap
 * @author guoxiaosong
 * @desc 所有在Bootstrap类中, 以_init开头的方法, 都会被Yaf调用,
 * @see http://www.php.net/manual/en/class.yaf-bootstrap-abstract.php
 * 这些方法, 都接受一个参数:Yaf_Dispatcher $dispatcher
 * 调用的次序, 和申明的次序相同
 */
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;
use Illuminate\Translation\Translator;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Translation\FileLoader;

class Bootstrap extends Yaf\Bootstrap_Abstract
{
    public function _initConfig()
    {
        // 把配置保存起来
        $config = Yaf\Application::app()->getConfig();
        Yaf\Registry::set('config', $config);
    }

    /**
     * Registry some classes what need to be pre-loaded.
     */
    public function _initClasses()
    {
        // Security class
        Yaf\Registry::set('Security', new \App\models\base\Security());

        // Translator class
        $filesystem = new Filesystem();
        $locale = Yaf\Registry::get('config')->get('application.locale');
        $path = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'messages';
        $loader = new FileLoader($filesystem, $path);
        $translator = new Translator($loader, $locale);
        $translator->setFallback('en');
        Yaf\Registry::set('Translator', $translator);

        // CookieCollection
        Yaf\Registry::set('CookieCollection', new \App\models\web\CookieCollection());

        // WebUser
        $webUser = new \App\models\web\User([
            'identityClass' => 'UserModel',
            'enableAutoLogin' => true
        ]);
        Yaf\Registry::set('WebUser', $webUser);
    }

    public function _initPlugin(Yaf\Dispatcher $dispatcher)
    {
        /*
         * layout allows boilerplate HTML to live in
         * /layouts/scripts rather than every script
         */
        $layout = new LayoutPlugin(
            Yaf\Registry::get('config')->get('layout.directory'),
            Yaf\Registry::get('config')->get('layout.file')
        );
        /*
         * Store a reference in the registry so values can be set later.
         *
         * This is a hack to make up for the lack of a getPlugin
         * method in the dispatcher.
         */
        Yaf\Registry::set('Layout', $layout);
        /* add the plugin to the dispatcher */
        $dispatcher->registerPlugin($layout);


    }

    public function _initRoute(Yaf\Dispatcher $dispatcher)
    {
        // 在这里注册自己的路由协议,默认使用简单路由
    }

    public function _initDatabase(Yaf\Dispatcher $dispatcher)
    {
        $config = Yaf\Registry::get('config');
        $database = $config->get('database')->toArray();

        $capsule = new Capsule;

        $capsule->addConnection($database);

        $capsule->setEventDispatcher(new Dispatcher(new Container));

        // Make this Capsule instance available globally via static methods... (optional)
        $capsule->setAsGlobal();

        // Setup the Eloquent ORM... (optional; unless you've used setEventDispatcher())
        $capsule->bootEloquent();

        Yaf\Registry::set('Capsule', $capsule->getConnection());
        Yaf\Registry::set('CapsuleDatabaseManager', $capsule->getDatabaseManager());
    }

    public function _initView(\Yaf\Dispatcher $dispatcher)
    {
        // 在这里注册自己的view控制器，例如smarty,firekylin
    }
}

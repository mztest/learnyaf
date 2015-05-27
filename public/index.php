<?php

define('APPLICATION_PATH', dirname(__DIR__));
define('APPLICATION_ENV', 'develop');

require APPLICATION_PATH . '/vendor/autoload.php';

$application = new \Yaf\Application( APPLICATION_PATH . "/config/application.ini", APPLICATION_ENV);

$application->bootstrap()->run();

<?php

use App\Bootstrap;
use \App\PhpCliServer;

require dirname(__DIR__) . '/vendor/autoload.php';

defined('DATA_DIR') || define('DATA_DIR', dirname(__DIR__) . '/data');
defined('BASE_DIR') || define('BASE_DIR', dirname(__DIR__));
defined('SRC_DIR') || define('SRC_DIR', BASE_DIR . '/src');
defined('WEB_DIR') || define('WEB_DIR', BASE_DIR . '/public');

if (PhpCliServer::handle(WEB_DIR)) {
    exit;
}

$app = Bootstrap::initApplication();
$app->run();
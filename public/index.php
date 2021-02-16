<?php

use App\Application;
use \App\PhpCliServer;

require dirname(__DIR__) . '/vendor/autoload.php';

defined('BASE_DIR') || define('BASE_DIR', dirname(__DIR__));
defined('DATA_DIR') || define('DATA_DIR', BASE_DIR . '/data');
defined('WEB_DIR') || define('WEB_DIR', BASE_DIR . '/public');

if (PhpCliServer::handle(WEB_DIR)) {
    exit;
}

(new Application())->run();
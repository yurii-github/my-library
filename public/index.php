<?php

use App\Bootstrap;
use \App\PhpCliServer;

require dirname(__DIR__) . '/vendor/autoload.php';

Bootstrap::initEnvironment(dirname(__DIR__) . '/data');

if (PhpCliServer::handle()) {
    exit;
}

$app = Bootstrap::initApplication();
$app->run();
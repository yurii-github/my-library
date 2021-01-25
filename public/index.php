<?php
require dirname(__DIR__) . '/vendor/autoload.php';
\App\Bootstrap::initEnvironment(dirname(__DIR__) . '/data');
$app = \App\Bootstrap::initApplication();
$app->run();
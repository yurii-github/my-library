<?php
require dirname(__DIR__) . '/vendor/autoload.php';
$app = \App\Bootstrap::initApplication(dirname(__DIR__) . '/data');
$app->run();
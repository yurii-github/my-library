<?php

if (1) {//todo: condition
    define('YII_DEBUG', true);
    define('YII_ENV', 'test'); //prod | test
    define('YII_ENABLE_ERROR_HANDLE', true);
}

$basedir = dirname(dirname(__DIR__));
require $basedir . '/vendor/autoload.php';
require $basedir . '/vendor/yiisoft/yii2/Yii.php';

$config = require $basedir . '/app/config/config.php';

if (file_exists($basedir . '/app/config/config.local.php')) {
    $config = \yii\helpers\ArrayHelper::merge($config, require $basedir . '/app/config/config.local.php');
}

(new yii\web\Application($config))->run();

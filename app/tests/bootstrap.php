<?php
define('YII_ENABLE_EXCEPTION_HANDLER', false);
define('YII_DEBUG', true);

$basedir = dirname(dirname(__DIR__));
/* @var $autoloader \Composer\Autoload\ClassLoader */
$autoloader = require $basedir . '/vendor/autoload.php';
$autoloader->addPsr4("app\\", $basedir . '/app');
$autoloader->addClassMap(['tests\AppTestCase' => __DIR__ . '/AppTestCase.php']);
require $basedir . '/vendor/yiisoft/yii2-dev/framework/Yii.php';

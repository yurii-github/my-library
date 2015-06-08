<?php
define('YII_ENABLE_EXCEPTION_HANDLER', false);
define('YII_DEBUG', true);

$base = dirname(dirname(__DIR__));
/* @var $a \Composer\Autoload\ClassLoader */
$a = require $base . '/vendor/autoload.php';
$a->addPsr4("app\\", $base . '/app');
//$a->addClassMap(['tests\DbTestCase' => __DIR__ . '/DbTestCase.php']);
require $base . '/vendor/yiisoft/yii2-dev/framework/Yii.php';
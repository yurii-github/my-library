<?php
define('YII_ENABLE_EXCEPTION_HANDLER', false);
define('YII_DEBUG', true);

/* @var $a \Composer\Autoload\ClassLoader */
$a = require dirname(__DIR__) . '/vendor/autoload.php';
$a->addPsr4("frontend\\", dirname(__DIR__) . '/frontend');
$a->addPsr4("common\\", dirname(__DIR__) . '/common');
$a->addClassMap(['tests\DbTestCase' => __DIR__ . '/DbTestCase.php']);
require dirname(__DIR__) . '/vendor/yiisoft/yii2-dev/framework/Yii.php';
<?php

if (1) {//todo: condition
	define('YII_DEBUG', true);
	define('YII_ENV', 'test'); //prod | test
	define('YII_ENABLE_ERROR_HANDLE', true);
}


$basedir = dirname(dirname(__DIR__));
require $basedir . '/vendor/autoload.php';
require $basedir . '/vendor/yiisoft/yii2/Yii.php';

if (YII_ENV == 'test') {
	//echo 'env';
	//$cfg = 
	//die;
}


(new yii\web\Application(
	\yii\helpers\ArrayHelper::merge(require $basedir.'/app/config/config.php',
	(file_exists($basedir.'/app/config/local.php') ? require $basedir.'/app/config/local.php' : [])) // for dev
))->run();
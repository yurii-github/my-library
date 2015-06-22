<?php
//define('YII_ENABLE_EXCEPTION_HANDLER', false);
define('YII_ENABLE_ERROR_HANDLE', false);
define('YII_DEBUG', true);
define('YII_ENV', 'test');

$basedir = dirname(dirname(__DIR__));
/* @var $autoloader \Composer\Autoload\ClassLoader */
$autoloader = require $basedir . '/vendor/autoload.php';
require $basedir . '/vendor/yiisoft/yii2-dev/framework/Yii.php';
$autoloader->addPsr4("app\\", $basedir . '/app');
$autoloader->addPsr4("modules\\", $basedir . '/app/modules');
$autoloader->addClassMap(['tests\AppTestCase' => __DIR__ . '/AppTestCase.php']);
$autoloader->addClassMap(['tests\AppFunctionalTestCase' => __DIR__ . '/AppFunctionalTestCase.php']);


// - - - - support for several databases to test
// config
$GLOBALS['db'] = [
	'sqlite' => [
		'dsn' => 'sqlite::memory:',
		'username' => null,
		'password' => null
	],
	'mysql' => [
		'dsn' => 'mysql:host=127.0.0.1;dbname=test-mylib',
		'username' => 'travis',
		'password' => null
	]
];

if (empty(getenv('DB_TYPE'))) {
	putenv('DB_TYPE=sqlite');	
}

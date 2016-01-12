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
$autoloader->addClassMap(['tests\AppUserTestCase' => __DIR__ . '/AppUserTestCase.php']);


//
// - - - - support for several databases to test
//
if (file_exists('local.dbconfig.php')) { // local config support.
	$GLOBALS['db'] = require 'local.dbconfig.php'; //must return array. see 'else' below. also set env as putenv('DB_TYPE=sqlite') if cannot set it outside
} else {
	$GLOBALS['db'] = [
		'sqlite' => [
			'dsn' => 'sqlite::memory:',
			// for mycfg -->
			'filename' => ':memory:',
		],
		'mysql' => [
			'dsn' => 'mysql:host=127.0.0.1;dbname=test_mylib',
			// for mycfg -->
			'host' => '127.0.0.1',
			'dbname' => 'test_mylib',
			'username' => 'travis',
			'password' => null
		]
	];
}

if (empty(getenv('DB_TYPE'))) {
	throw new \Exception('must setup env variable DB_TYPE. Supported values are \'mysql\' and \'sqlite\'');
}


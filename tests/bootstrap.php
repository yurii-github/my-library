<?php
die;
return;

//
// - - - - support for several RDBMS to test
//
$localConfig = $basedir . '/app/tests/local.dbconfig.php';
if (file_exists($localConfig)) { // local config support.
	$GLOBALS['db'] = require_once $localConfig; //must return array. see 'else' below. also set env as putenv('DB_TYPE=sqlite') if cannot set it outside
} else {
	$GLOBALS['db'] = [
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


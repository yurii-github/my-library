<?php

return 
[

	'id' => 'mylibray-console',
	'basePath' => dirname(__DIR__),
	'aliases' => [
		'@console' => '@app'
	],
	'controllerMap' => [
		'migrate' => [
			'class' => '\console\controllers\MigrateController',
			'migrationTable' => 'yii2_migrations',
			'migrationPath' => dirname(__DIR__) . '/migrations/',
			'interactive' => 0,
			'templateFile' => dirname(__DIR__) . '/migrations/_template.php',
			'db' => 'db'
		]
	],
	
];
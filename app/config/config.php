<?php
return [
	'charset' => 'utf-8',
	'id'		=> 'mylib',
	'name'		=> 'MyLibrary',
	'basePath'	=> dirname(__DIR__),
	'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
	'defaultRoute' => 'site',
	'layout' => 'main',
	'controllerNamespace' => 'app\controllers',
	'params' => [],
	'bootstrap' => [
		[ 'class' => app\components\MyLibraryBootstrap::class],
	],
	'aliases' => [
		'@console' => '@app/../console',
		'@modules' => '@app/modules',
		'@runtime' => '@app/runtime',
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
	],
	'language' => 'en-US',
	'modules' => [
	],
	'components' => [
		'i18n' => [
			'translations' => [
				'frontend/*' => [
					'class' => \yii\i18n\PhpMessageSource::class,
					'basePath' => '@app/i18n',
					'sourceLanguage' => 'en-US'
				]
			],
		],
		'request' => [
            'cookieValidationKey' => 'asd',
            'enableCsrfValidation' => false],
        'view' => [
            'theme' => [ //TODO: add themes
                'pathMap' => ['@app/views' => '@app/themes/basic'],
                'baseUrl' => '@web/themes/basic'
            ]
        ],
		'urlManager' => [
			'class' => \yii\web\UrlManager::class,
			'enablePrettyUrl' => true,
			'showScriptName' => false,
			'enableStrictParsing' => false,
			'rules' => [
				'' => 'site/index',
				'config' => 'config/index',
				'about' => 'site/about',
				'install' => 'install/install',
			]
		],
		'mycfg' => [
			'class' =>  \app\components\Configuration::class,
			'config_file' => '@app/config/libconfig.json',
            'version' => '1.3',
		],
		'log' => [
			'traceLevel' => YII_DEBUG ? 1 : 0,
			'targets' => [
				'dev-trace'=> [
					'class' => \yii\log\FileTarget::class,
					'microtime' => true,
					'enabled' => YII_DEBUG ? true : false,
					'levels' => ['trace', 'profile'],
					'categories' => ['events'],
					'logVars' => [],
					'logFile' => '@app/data/logs/dev-trace.txt',
					'maxFileSize' => 1024, // 1mb
					'maxLogFiles' => 1,
					'enableRotation' => true,
				],
				'info'=> [
					'class' => \yii\log\FileTarget::class,
					'microtime' => true,
					'enabled' => false,
					'levels' => ['profile', 'info'],
					'categories' => ['events'],
					'logVars' => [],
					'logFile' => '@app/data/logs/info.txt',
					'maxFileSize' => 1024, //1mb
					'maxLogFiles' => 1,
					'enableRotation' => true,
				],
				'errors' => [
					'class' => \yii\log\FileTarget::class,
					'enabled' => true,
					'levels' => ['warning', 'error'],
					//'categories' => ['application'],
					'logVars' => [],
					'logFile' => '@app/data/logs/errors.txt',
					'maxFileSize' => 1024, //1mb
					'maxLogFiles' => 1,
				],
			]
		],
		'cache' => [
		    'class' => \yii\caching\MemCache::class,
            'useMemcached' => true,
			//'class' => \app\components\ApcCache::class,
			//'class' => \yii\caching\DummyCache::class,
			'keyPrefix' => 'mylib::',
		],
		'authManager' => [
			'class' => \yii\rbac\DbManager::class,
			'cache' => 'cache'
			//'defaultRoles' => []
		],
		'db' => [
			'class' => \yii\db\Connection::class,
			'enableSchemaCache' => true,
			'schemaCache' => 'cache',
			'schemaCacheDuration' => 3600,
			'dsn' => null, // will be overridden!
			'charset' => 'UTF8' // utf-8 fails?
		],
	],
];

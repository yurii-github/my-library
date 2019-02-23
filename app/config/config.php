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
	//'controllerMap' => [alias=>class],
	//'catchAll' => ['site/offline'],
	'params' => [],
	'bootstrap' => [
		[ 'class' => app\components\MyLibraryBootstrap::class],
		//'log',
        //'debug',
	/*
		[	'class' => 'yii\filters\ContentNegotiator',
			//'only' => ['view'],
			'formats' => [
				'text/html' => \yii\web\Response::FORMAT_HTML,
				'application/json' => \yii\web\Response::FORMAT_JSON,
				'application/xml' => \yii\web\Response::FORMAT_XML,
			],
			'languages' => [ 'en' => 'en-USSSS', 'ua' => 'ua' ],
		],*/
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
		//'apc' => [ 'class' => 'modules\apc\Module' ],
		//'debug' => [ 'class' => 'yii\debug\Module' ]
	],
	'components' => [
		/*'errorHandler' => [
		 //'class' => 'yii\web\ErrorAction'
		 //'errorAction' => 'site/error'
		],*/
		'i18n' => [
			'translations' => [
				'frontend/*' => [
					'class' => 'yii\i18n\PhpMessageSource',
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
        /*
		'assetManager' => [
			//'appendTimestamp' => true,
			'converter' => [
				'class' => 'yii\web\AssetConverter'
			],
			//'class' => 'app\components\AssetManager',
			//'linkAssets' => false, // symblic linking is OFF
			//'basePath' => '@webroot/assets',
		],*/
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
		// https://github.com/yiisoft/yii2/blob/master/docs/guide/runtime-logging.md
		//TODO: cannot set Logger or Dispatcher class. yii2 issue?
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
					'class' => '\yii\log\FileTarget',
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
			//'class' => \app\components\ApcCache::class,
			'class' => '\yii\caching\DummyCache',
			'keyPrefix' => 'mylib::',
		],
		'authManager' => [
			'class' => '\yii\rbac\DbManager',
			'cache' => 'cache'
			//'defaultRoles' => []
		],
		'db' => [
			'class' => \yii\db\Connection::class,
			'enableSchemaCache' => true,
			'schemaCache' => 'cache',
			'schemaCacheDuration' => 3600,
			'dsn' => null, // will be overriden!
			'charset' => 'UTF8' // utf-8 fails?
		],
	],
];

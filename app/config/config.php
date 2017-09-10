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
		[ 'class' => 'app\components\MyLibraryBootstrap' ],
		'log', 'debug', //'gii'
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
		'@bower' => '@vendor/bower-asset' //TODO: remove
	],
	'language' => 'en-US',
	'modules' => [
		//'apc' => [ 'class' => 'modules\apc\Module' ],
		//'gii' => [ 'class' => 'yii\gii\Module' ],
		'debug' => [ 'class' => 'yii\debug\Module' ]
	],
	'components' => [
		'security' => [
			'class' => app\components\Security::class,
			'cost' => 10
		],
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
			'cookieValidationKey' => 'asd' ,
			'enableCsrfValidation' => false],
			'view' => [
				'theme' => [ //TODO: add themes
					'pathMap' => [ '@app/views' => '@app/themes/basic' ],
					'baseUrl' => '@web/themes/basic'
				]
			],
		'assetManager' => [
			//'appendTimestamp' => true,
			'converter' => [
				'class' => 'yii\web\AssetConverter'
			],
			'class' => 'app\components\AssetManager',
			'linkAssets' => false, // symblic linking is OFF
			'basePath' => '@webroot/assets',
			'bundles' => [			  
			  // resets Yii2 nonsense
			  'yii\widgets\MaskedInputAsset' => ['js' => ['https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/3.3.4/jquery.inputmask.bundle.min.js'], 'sourcePath' => null],
			  'yii\widgets\PjaxAsset' => ['js' => ['https://cdnjs.cloudflare.com/ajax/libs/jquery.pjax/1.9.6/jquery.pjax.min.js'], 'sourcePath' => null],
			  'yii\validators\PunycodeAsset' => ['js' => ['https://cdnjs.cloudflare.com/ajax/libs/punycode/1.4.1/punycode.min.js'], 'sourcePath' => null],
			  'yii\web\JqueryAsset' => ['js'=> ['https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.4/jquery.min.js'], 'sourcePath' => null],
			  //'yii\web\JqueryAsset' => ['js'=> ['https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js'], 'sourcePath' => null], <-- JqueryUI not supports
			  'yii\bootstrap\BootstrapAsset' => ['css' => ['https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css'], 'sourcePath' => null],
			  'yii\bootstrap\BootstrapPluginAsset' => ['js' => ['https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js'], 'sourcePath' => null],
			  'yii\gii\TypeAheadAsset' => ['js' => ['https://cdnjs.cloudflare.com/ajax/libs/corejs-typeahead/1.1.1/typeahead.bundle.min.js'], 'sourcePath' => null],
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
		'mailer' => [
			'class' => 'yii\swiftmailer\Mailer',
			'useFileTransport' => false,
			'viewPath' => '@app/emails',
			'fileTransportPath' => '@runtime/mail',
			'htmlLayout' => 'layouts/html',
			'textLayout' => 'layouts/text',
			'transport' => [
				'class' => 'Swift_SmtpTransport',
				'host' => 'smtp.sample.com',
				'username' => 'test',
				'password' => 'test',
				'port' => '465',
				'encryption' => 'ssl' // ssl | tls
			]
		],
		'mycfg' => [
			'class' => 'app\components\Configuration',
			'config_file' => '@app/config/libconfig.json'
		],
		// https://github.com/yiisoft/yii2/blob/master/docs/guide/runtime-logging.md
		//TODO: cannot set Logger or Dispatcher class. yii2 issue?
		'log' => [ 
			'traceLevel' => YII_DEBUG ? 3 : 0,
			'targets' => [
				'dev-trace'=> [
					'class' => 'app\components\log\FileTarget',
					'with_microtime' => true,
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
					'class' => 'app\components\log\FileTarget',
					'with_microtime' => true,
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
		
		/*'errorHandler' => [
			//'class' => 'yii\web\ErrorAction'
			//'errorAction' => 'site/error'
		],*/
		'user'=> [
			'identityClass' => \app\models\Users::class,
			'enableAutoLogin' => true,
			'loginUrl' => ['site/index'],
			'enableSession' => true
		],
		'authManager' => [
			'class' => '\yii\rbac\DbManager',
			'cache' => 'cache'
			//'defaultRoles' => []
		],
		'request' => [
			'cookieValidationKey' => 'asd' , 
			'enableCsrfValidation' => false],
		'view' => [
			'theme' => [ //TODO: add themes
				'pathMap' => [ '@app/views' => '@app/themes/basic' ],
				'baseUrl' => '@web/themes/basic'
			]
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
<?php
/*
 * My Book Library
 *
 * Copyright (C) 2014-2019 Yurii K.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see http://www.gnu.org/licenses
 */

defined('YII_ENV') || define('YII_ENV', 'prod'); // prod | dev | test
defined('YII_DEBUG') || define('YII_DEBUG', YII_ENV !== 'prod');
defined('YII_ENABLE_ERROR_HANDLE') || define('YII_ENABLE_ERROR_HANDLE', YII_ENV === 'prod');

return [
	'charset' => 'utf-8',
	'id'		=> 'mylib',
	'name'		=> 'MyLibrary',
	'basePath'	=> dirname(__DIR__),
	'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
	'defaultRoute' => 'site',
	'layout' => 'main',
	'controllerNamespace' => 'app\controllers',
	'bootstrap' => [
		[ 'class' => app\components\MyLibraryBootstrap::class],
	],
	'aliases' => [
        '@data'  => dirname(__DIR__, 2) .'/data',
	],
	'language' => 'en-US',
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
		    'enableCookieValidation' => false,
            'enableCsrfValidation' => false,
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
			'config_file' => '@data/config.json',
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
					'logFile' => '@data/logs/dev-trace.txt',
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
					'logFile' => '@data/logs/info.txt',
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
					'logFile' => '@data/logs/errors.txt',
					'maxFileSize' => 1024, //1mb
					'maxLogFiles' => 1,
				],
			]
		],
		'cache' => [
            'class' => \yii\caching\DummyCache::class,
			'keyPrefix' => 'mylib::',
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

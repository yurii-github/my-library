<?php
//
// configuration for console
//
$console_config = [];
$config = require __DIR__.'/config.php';
$mylib_config_file = dirname(__DIR__).'/data/config.json';

$console_config = [
  'id' => $config['id'].'-console',
  'basePath' => $config['basePath'],
  'components' => [
    'db' => $config['components']['db']
  ],
  'controllerNamespace' => 'yii\console\controllers',
  'controllerMap' => [
    'migrate' => [
      'class' => \yii\console\controllers\MigrateController::class,
      'migrationTable' => 'yii2_migrations',
      'interactive' => 0,
    ]
  ]
];

if (file_exists($mylib_config_file)) {
  $mylib_config = json_decode(file_get_contents($mylib_config_file));
  $console_config['components']['db']['dsn'] = 'sqlite:'.$mylib_config->database->filename;
}

return $console_config;

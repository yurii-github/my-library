<?php
//
// configuration for console
//
$console_config = [];
$config = require __DIR__.'/config.php';
if (file_exists(__DIR__ . '/config.local.php')) {
    $config = \yii\helpers\ArrayHelper::merge($config, require __DIR__ . '/config.local.php');
}

$settingsFile = $config['aliases']['@data'].'/config.json';

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

if (file_exists($settingsFile)) {
  $settings = json_decode(file_get_contents($settingsFile));
  $db = &$console_config['components']['db'];
  if ($settings->database->format === 'sqlite') {
      $db['dsn'] = "sqlite:host={$settings->database->host};dbname={$settings->database->dbname}";
  } else {
      $db['dsn'] = "{$settings->database->format}:host={$settings->database->host};dbname={$settings->database->dbname}";
      $db['username'] = $settings->database->login;
      $db['password'] = $settings->database->password;
  }
}

return $console_config;

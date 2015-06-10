<?php
//defined('YII_DEBUG') or define('YII_DEBUG', true);

// fcgi doesn't have STDIN and STDOUT defined by default
defined('STDIN') or define('STDIN', fopen('php://stdin', 'r'));
defined('STDOUT') or define('STDOUT', fopen('php://stdout', 'w'));


require(__DIR__ . '/vendor/autoload.php');
require(__DIR__ . '/vendor/yiisoft/yii2/Yii.php');

$config = require(__DIR__ . '/console/config/main.php');
$cfg_main = require(__DIR__ . '/frontend/config/web.php');
if (file_exists(__DIR__.'/data/libconfig.json')) {
	$mylib = json_decode(file_get_contents(__DIR__.'/data/libconfig.json'));
	// TODO: make single interface among web adn console
	$cfg_main['components']['db']['dsn'] = 'sqlite:'.$mylib->database->filename;
}
$cfg_fixed['components']['db'] = $cfg_main['components']['db'];
$cfg_fixed['components']['authManager'] = $cfg_main['components']['authManager'];
//fix condif for console app / remove failing stuff
unset($cfg_main);

$application = new yii\console\Application(yii\helpers\ArrayHelper::merge($config, $cfg_fixed));
$exitCode = $application->run();
//exit($exitCode);
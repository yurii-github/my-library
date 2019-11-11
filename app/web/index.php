<?php

require '../../vendor/autoload.php';
$config = require '../config/config.php';
$isDevMode = $config['params']['mode'] === 'dev';

define('YII_DEBUG', $isDevMode);
define('YII_ENV', $isDevMode ? 'dev' : 'prod'); // prod | dev | test
define('YII_ENABLE_ERROR_HANDLE', $isDevMode);

require '../../vendor/yiisoft/yii2/Yii.php';

if (file_exists('../config/config.local.php')) {
    $config = \yii\helpers\ArrayHelper::merge($config, require '../config/config.local.php');
}

(new yii\web\Application($config))->run();

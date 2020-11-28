<?php

require '../../vendor/autoload.php';
$config = require '../config/config.php';
require '../../vendor/yiisoft/yii2/Yii.php';

if (file_exists('../config/config.local.php')) {
    $config = \yii\helpers\ArrayHelper::merge($config, require '../config/config.local.php');
}

(new yii\web\Application($config))->run();

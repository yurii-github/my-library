<?php

/** @var \Composer\Autoload\ClassLoader $autoload */
$autoload = require dirname(__DIR__).'/vendor/autoload.php';

// https://github.com/laravel/framework/tree/master/src/Illuminate/Testing/Constraints
$autoload->addClassMap([
    \Illuminate\Testing\Constraints\CountInDatabase::class => __DIR__ .'/Constraints/CountInDatabase.php',
    \Illuminate\Testing\Constraints\HasInDatabase::class => __DIR__ .'/Constraints/HasInDatabase.php',
    \Illuminate\Testing\InteractsWithDatabase::class => __DIR__ .'/Constraints/InteractsWithDatabase.php',
    \Illuminate\Testing\Constraints\SoftDeletedInDatabase::class => __DIR__ .'/Constraints/SoftDeletedInDatabase.php',
]);
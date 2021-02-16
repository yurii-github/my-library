<?php

/** @var \Composer\Autoload\ClassLoader $autoload */
$autoload = require dirname(__DIR__) . '/vendor/autoload.php';

// https://github.com/laravel/framework/tree/master/src/Illuminate/Testing/Constraints
$autoload->addClassMap([
    \Illuminate\Testing\Constraints\CountInDatabase::class => __DIR__ . '/Constraints/CountInDatabase.php',
    \Illuminate\Testing\Constraints\HasInDatabase::class => __DIR__ . '/Constraints/HasInDatabase.php',
    \Illuminate\Testing\InteractsWithDatabase::class => __DIR__ . '/Constraints/InteractsWithDatabase.php',
    \Illuminate\Testing\Constraints\SoftDeletedInDatabase::class => __DIR__ . '/Constraints/SoftDeletedInDatabase.php',
]);

if (file_exists(__DIR__ . '/local_test_env.php')) {
    require __DIR__ . '/local_test_env.php';
} else {
    if (!isset($_ENV['DB_TYPE'])) {
        putenv('DB_TYPE=sqlite_memory'); // sqlite_memory | sqlite | mysql
    }
    putenv('DB_DBNAME=test_mylib');
    putenv('DB_LOGIN=travis');
    putenv('DB_PASSWORD=');
}
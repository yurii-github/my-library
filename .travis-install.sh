#!/bin/bash

if [ -d vendor/bin ]
then
  echo "\e[43musing cache. nothing to do";
else
  echo "\e[43mgetting latest PHPUnit";
  wget https://phar.phpunit.de/phpunit.phar -O yurii/phpunit.phar --no-check-certificate
  echo "\e[43mremoving dev deps as we have ones in CI or not required for testing";
  composer remove almasaeed2010/adminlte --no-update
  composer remove yiisoft/yii2-debug phpunit/phpunit phpunit/dbunit --dev --no-update
  echo "\e[43mdownloading dependencies...";
  composer install --prefer-dist
  composer require codeclimate/php-test-reporter --prefer-dist
fi

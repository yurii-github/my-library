#!/bin/bash

if [ -d vendor/bin ]
then
  echo "using cache. nothing to do";
else
  echo "getting latest PHPUnit";
  wget https://phar.phpunit.de/phpunit.phar -O phpunit.phar -P .
  echo "removing dev deps as we have ones in CI or not required for testing";
  composer remove almasaeed2010/adminlte --no-update
  composer remove yiisoft/yii2-debug phpunit/phpunit phpunit/dbunit --dev --no-update
  echo "downloading dependencies...";
  composer install --prefer-dist
  composer require codeclimate/php-test-reporter --prefer-dist
fi

#!/bin/bash

if [ -d vendor/bin ]
then
  echo -e "\e[0;34;40m using cache. nothing to do";
else
  echo -e "\e[0;34;40m getting latest PHPUnit";
  wget https://phar.phpunit.de/phpunit.phar -O vendor/phpunit.phar --no-check-certificate
  echo -e "\e[0;34;40m removing dev deps as we have ones in CI or not required for testing";
  composer remove almasaeed2010/adminlte --no-update
  composer remove yiisoft/yii2-debug phpunit/phpunit phpunit/dbunit --dev --no-update
  echo -e "\e[0;34;40m downloading dependencies...";
  composer install --prefer-dist
  composer require codeclimate/php-test-reporter --prefer-dist
fi

#!/bin/bash

if [ -d vendor/bin ]
then
  echo "^[[0;37;43musing cache. nothing to do";
else
  echo "^[[0;37;43mgetting latest PHPUnit";
  wget https://phar.phpunit.de/phpunit.phar -O yurii/phpunit.phar --no-check-certificate
  echo "^[[0;37;43mremoving dev deps as we have ones in CI or not required for testing";
  composer remove almasaeed2010/adminlte --no-update
  composer remove yiisoft/yii2-debug phpunit/phpunit phpunit/dbunit --dev --no-update
  echo "^[[0;37;43mdownloading dependencies...";
  composer install --prefer-dist
  composer require codeclimate/php-test-reporter --prefer-dist
fi

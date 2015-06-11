#!/bin/bash

if [ -d vendor/bin ]
then
  echo "using cache. nothing to do";
else
  echo "removing phpunit as we have once in CI";
  composer remove phpunit/phpunit phpunit/dbunit --no-update  
  echo "downloading dependencies...";
  composer install --prefer-dist
  composer require codeclimate/php-test-reporter --prefer-dist
fi

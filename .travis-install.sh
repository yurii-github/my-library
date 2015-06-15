#!/bin/bash

color="\e[0;34;40m";

echo -e "${color}checking for [skip clover]..";
if git log -1 --oneline | grep -iqw "[skip clover]" 
then
  echo -e "${color}message presented. skipping clover";
  export CLOVER='';
else
  echo -e "${color}message not presented. setting clover..";
  export CLOVER="--coverage-clover ../../build/logs/clover.xml";
  echo -e "${color}clover set to $CLOVER";
fi


if [ -d vendor/bin ]
then
  echo -e "${color}using cache. nothing to do";
else
  echo -e "${color}getting latest PHPUnit";
  wget https://phar.phpunit.de/phpunit.phar -O vendor/phpunit.phar --no-check-certificate
  
  echo -e "${color}setting github oauth token..";
  composer config -g github-oauth.github.com $GITHUB_TOKEN

  echo -e "${color}removing dev deps as we have ones in CI or not required for testing";
  composer remove almasaeed2010/adminlte --dev --no-update
  composer remove yiisoft/yii2-debug --dev --no-update
  composer remove  phpunit/phpunit phpunit/dbunit --dev --no-update
  echo -e "${color}downloading required dependencies...";
  composer require codeclimate/php-test-reporter --no-update
  composer install --prefer-dist --optimize-autoloader --no-progress
  echo -e "${color}show installed dependencies:";
  composer show --installed
fi

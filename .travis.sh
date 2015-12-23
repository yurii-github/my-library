#!/bin/bash

color="\e[0;34;40m";

#
# INSTALL 
#
if [ "$1" == "install" ]
then
	# cache usage
	#
	if [ -d vendor/bin ]
	then
		echo -e "${color}Using cache.";
		#
		echo -e "${color}Loading cached apcu.so for PHP";
		echo -e "extension = $(pwd)/vendor/apcu.so\napc.enabled=1\napc.enable_cli=1" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
	else
		if [ ${TRAVIS_PHP_VERSION:0:3} != "5.6" ] && [ ${DB_TYPE} -ne "sqlite" ]
		then
			echo -e "${color}Cache install is not allowed. Must be PHP 5.6 and sqlite"
			exit 500
		fi

		echo -e "${color}getting latest Selenium Server Standalone";
		wget http://goo.gl/PJUZfa -O vendor/selenium.jar
	
		echo -e "${color}getting latest Chrome WebDriver for Selenium Server Standalone";
		#http://chromedriver.storage.googleapis.com/2.20/chromedriver_linux64.zip
		wget http://chromedriver.storage.googleapis.com/2.20/chromedriver_linux32.zip -O chrome32.zip
		unzip -j chrome32.zip chromedriver
		mv chromedriver vendor/chromedrv-32		
		composer self-update
		# installing apcu. apcu 5+ not compatible with php 5.6. it will be loaded this time by pear
		echo -e "${color}installing APCu 4.0.10 via PEAR/PECL..."
		echo 'yes' | pecl install apcu-4.0.10
		cp $(pear config-get ext_dir)/apcu.so $(pwd)/vendor/apcu.so
		
		echo -e "${color}getting latest PHPUnit..."
		wget https://phar.phpunit.de/phpunit.phar -O vendor/phpunit.phar --no-check-certificate
	  
		echo -e "${color}setting github oauth token..";
		composer config -g github-oauth.github.com $GITHUB_TOKEN

		echo -e "${color}removing dev deps as we have ones in CI or not required for testing";
		composer remove yiisoft/yii2-debug --dev --no-update
		composer remove phpunit/phpunit phpunit/dbunit --dev --no-update
		echo -e "${color}downloading required dependencies...";
		composer require codeclimate/php-test-reporter --no-update
		composer install --prefer-dist --optimize-autoloader --no-progress
		echo -e "${color}show installed dependencies:";
		composer show --installed
	fi
	
	exit $?
fi


#
# SCRIPT
#
if [ "$1" == "script" ]
then
	cd app/tests
	# if php5.6 use clover
	if [ ${TRAVIS_PHP_VERSION:0:3} == "5.6" ] && [ ${DB_TYPE} == "sqlite" ]
	then
		php ../../vendor/phpunit.phar $CLOVER
	else
		php ../../vendor/phpunit.phar
	fi

	export RES=$?
	cd ../..
	
	exit $RES
fi

#
# AFTER SUCCESS
#
if [ "$1" == "after_success" ]
then
	# if php5.6 use clover
	if [ ${TRAVIS_PHP_VERSION:0:3} == "5.6" ] && [ ${DB_TYPE} == "sqlite" ] && [ -n "$CLOVER" ]
	then
		vendor/bin/test-reporter
	else
		echo -e "${color}skipping codeclimate reporter"; 
	fi
	
	exit $?
fi




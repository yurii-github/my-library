#!/bin/bash

color="\e[0;34;40m";

#
# ISNTALL 
#
if [ "$1" == "install" ]
then
	# clover usage
	#
	echo -e "${color}checking for [sc] (skip clover)..";
	if git log -1 --oneline | grep -qie "\[sc\]" 
	then
		echo -e "${color}[sc] presented. removing clover..";
		unset CLOVER;
	else
		echo -e "${color}[sc] not presented. setting clover..";
		setenv("CLOVER", "--coverage-clover ../../build/logs/clover.xml");
	fi

	# cache usage
	#
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
	
	exit 0;
fi


#
# SCRIPT
#
if [ "$1" == "script" ]
then
	echo -e "${color}CLOVER=$CLOVER";
	cd app/tests
	php ../../vendor/phpunit.phar --testsuite=$TEST_SUITE $CLOVER
	cd ../..
	
	exit 0;
fi

#
# AFTER SCRIPT
#
if [ "$1" == "after_script" ]
then
	# clover usage
	#
	if [ -n "$CLOVER" ]
	then
		vendor/bin/test-reporter
	else
		echo -e "${color}skipping codeclimate reporter as clover was disabled by commit message";
	fi
	
	exit 0;
fi




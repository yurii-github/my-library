#!/bin/bash

color="\e[0;34;40m";

function install()
{
	case $1 in

		selenium*)
			echo -e "${color}getting latest Selenium Server Standalone";
			wget http://goo.gl/PJUZfa -O vendor/selenium.jar
			;;

		apcu*)
			echo -e "${color}installing APCu via PEAR/PECL..."
			if [ "${TRAVIS_PHP_VERSION:0:3}" == "7.4" ]
			then
			  echo -e "${color}PHP 7.4 detected. Skipped due to errors in PEAR."
			else
			  echo 'yes' | pecl install apcu-5.1.18
			  cp $(pear config-get ext_dir)/apcu.so $(pwd)/vendor/apcu.so
			fi
			;;

		chromium*)
			# TODO: fails to run
			# https://commondatastorage.googleapis.com/chromium-browser-snapshots/index.html?prefix=Linux_x64/368894/
			echo -e "${color}Installing Chromium...";
			wget "https://www.googleapis.com/download/storage/v1/b/chromium-browser-snapshots/o/Linux_x64%2F368894%2Fchrome-linux.zip?generation=1452617615555000&alt=media" -O chrome.zip --no-check-certificate
			unzip chrome.zip
			mv chrome-linux vendor

			# TODO: get how to install google chrome w/o admin rights
			#wget https://dl.google.com/linux/direct/google-chrome-stable_current_amd64.deb -O chrome.deb --no-check-certificate
			#ar vx chrome.deb
			#mkdir yk_chrome
			#tar -xf data.tar.xz -C yk_chrome
			#mv yk_chrome vendor
		;;

		chromedriver*)
			echo -e "${color}Getting latest Chrome WebDriver for Selenium Server Standalone";
			wget http://chromedriver.storage.googleapis.com/2.20/chromedriver_linux64.zip -O chromedriver.zip
			unzip -j chromedriver.zip chromedriver
			mv chromedriver vendor/chromedrv
			chmod +x vendor/chromedrv
			;;

		deps*)
			echo -e "${color}downloading required dependencies...";
			if [ "${TRAVIS_PHP_VERSION:0:3}" == "7.2" ]
	    then
		    composer require codeclimate/php-test-reporter --no-update
	    fi
			composer install --prefer-dist --optimize-autoloader --no-progress
			echo -e "${color}show installed dependencies:";
			composer show --installed
			;;

		*)
		echo 'Unknown parameter provided for install()'
		;;
	esac
}


php --info | grep -i gd
exit
#
# INSTALL
#
if [ "$1" == "install" ]
then
	# with cache usage
	if [ -d vendor/bin ]
	then
		echo -e "${color}Using cache.";
		echo -e "${color}Loading cached apcu.so for PHP";
		echo -e "extension = $(pwd)/vendor/apcu.so\napc.enabled=1\napc.enable_cli=1" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
	else
		echo -e "${color}Update Composer and set github oauth token..";
		composer self-update
		composer config -g github-oauth.github.com $GITHUB_TOKEN

		install apcu
		#install selenium
		#install chromium
		#install chromedriver
		install deps

		echo -e "${color}DEBUG: show vendor dir. IT will be cached";
		ls vendor -l

	fi

	exit $?
fi


#
# SCRIPT
#
if [ "$1" == "script" ]
then
	# if php7.2 use clover
	if [ "${TRAVIS_PHP_VERSION:0:3}" == "7.2" ] && [ "${DB_TYPE}" == "sqlite" ]
	then
		./phpunit $CLOVER
	else
		./phpunit
	fi

	export RES=$?
	exit $RES
fi

#
# AFTER SUCCESS
#
if [ "$1" == "after_success" ]
then
	# if php7.2 use clover
	if [ "${TRAVIS_PHP_VERSION:0:3}" == "7.2" ] && [ "${DB_TYPE}" == "sqlite" ] && [ -n "$CLOVER" ]
	then
		./vendor/bin/test-reporter
	else
		echo -e "${color}skipping codeclimate reporter";
	fi

	exit $?
fi




#!/bin/bash

if [ -d vendor/bin ]
then
  echo 'using cache. nothing to do.';
else
 echo 'downloading dependencies..';
 composer install --prefer-dist
fi

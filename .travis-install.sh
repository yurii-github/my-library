#!/bin/bash

if [ -d vendor ]
then
  echo 'using cache. nothing to do.';
else
 echo 'downloading dependencies..';
 composer install --prefer-dist
fi

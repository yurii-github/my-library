#!/bin/bash

# if cache is not set, download dependencies
if [ ! -d /home/travis/build/yurii-github/yii2-mylib/vendor ]
then
    composer install --prefer-dist
fi

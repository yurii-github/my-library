#!/bin/bash

# if cache is not set, download dependencies
if [ ! -d vendor ]
then
    composer install --prefer-dist
fi

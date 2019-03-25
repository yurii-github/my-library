
# My Library

[![Build Status](https://travis-ci.org/yurii-github/yii2-mylib.svg?branch=master)](https://travis-ci.org/yurii-github/yii2-mylib) [![Code Climate](https://codeclimate.com/github/yurii-github/yii2-mylib/badges/gpa.svg)](https://codeclimate.com/github/yurii-github/yii2-mylib) [![Test Coverage](https://codeclimate.com/github/yurii-github/yii2-mylib/badges/coverage.svg)](https://codeclimate.com/github/yurii-github/yii2-mylib/coverage) [![Yii2](https://img.shields.io/badge/Powered_by-Yii_Framework-green.svg?style=flat)](http://www.yiiframework.com/)

## About

This is book library manager, written in PHP and based on JqueryUI.  
The main purpose of it is to manage own book library as excel table with ratings and other stuff like filename syncronizations of your books and more.  
It supports all JQuery UI themes.  

![library page](app/public/library.png)


## Functionality

- excel table is sortable, pagable
- books CRUD
- books categories
- book cover is saved to database
- book cover can be dropped in web interface
- book cover can be imported from PDF (you need to have [ghostScript](https://www.ghostscript.com/))
- web interface is fully themed (all JqueryUI themes)
- i18n
- MySQL and SQLite support
- synchronization with filesystem (renames, deletes)
- migrations
- compact: optimizes DB via run vacuum for SQLite or table optimization for MySQL
- 1-hour caching for images from DB (if you setup cache in config)
- configuration is done via web interface

## Requirements

- PHP 7.2+
- SQLite or MySQL database
- Internet connection (JS/CSS comes from CDN networks, so if you want to work offline you should check code at /app/assets/*)

## Setup & Run
 
I do recommend to use github approach because you can get more fresh code

* Github
    ```
    git clone git@github.com:yurii-github/yii2-mylib.git
    cd yii2-mylib
    composer update
    ./yii serve -t @app/public
    ```
* Packagist/Composer - [https://packagist.org](https://packagist.org/packages/yurii-github/yii2-mylib)
    ```
    composer create-project  yurii-github/yii2-mylib  --prefer-dist --stability=stable --no-dev
    cd yii2-mylib
    ./yii serve -t @app/public
    ```

Application will be available at [http://localhost:8080](http://localhost:8080)

## Structure
Here's the list of most interesting places: 
<pre>
├── app
│   ├── assets      -  -  -  -  -  -  -  - CDN assets
│   ├── config
│   │   └── config.local.php -  -  -  -  - your custom app configuration (that cannot be set via settings)
│   ├── public      -  -  -  -  -  -  -  - entry point of the application  
├── data            -  -  -  -  -  -  -  - your data is stored here
│   ├── config.json          -  -  -  -  - settings set via web interface
│   └── mydb.s3db            -  -  -  -  - book SQLite database
└── yii             -  -  -  -  -  -  -  - console app
</pre>

## Sync
If you have enabled synchronization application will sync changes to your filesystem
* any cell change that has influence on filename of the book will cause its rename
* if you delete book it will be removed from filesystem too 
* you cannot remove records without corresponding real file (you may disable sync if required)
* you have to manage file extension manually to rename file properly.
* if you change book filename format it will not rename all books! It will apply new format only to newly renamed books

## Importing new books
The simplest way is to drop your books to library folder you've set in configuration and then on "synchronization" press "import fs only". 
It will import all file system books that are not in database yet.


# Development

To export translations, run
```
./yii message app/config/message-config.php
```

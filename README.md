
<img src="http://gregfranko.com/images/jqueryui.png"/> 

[![Build Status](https://travis-ci.org/yurii-github/yii2-mylib.svg?branch=master)](https://travis-ci.org/yurii-github/yii2-mylib) [![Code Climate](https://codeclimate.com/github/yurii-github/yii2-mylib/badges/gpa.svg)](https://codeclimate.com/github/yurii-github/yii2-mylib) [![Test Coverage](https://codeclimate.com/github/yurii-github/yii2-mylib/badges/coverage.svg)](https://codeclimate.com/github/yurii-github/yii2-mylib/coverage) [![Yii2](https://img.shields.io/badge/Powered_by-Yii_Framework-green.svg?style=flat)](http://www.yiiframework.com/)


![library page](app/public/library.png)

# About

This is book library manager, written in PHP and based on JqueryUI.  
The main purpose of it is to manage own book library as excel table with ratings and other stuff like filename syncronizations of your books and more.  
It supports all JQuery UI themes.  

## Functionality

- excel table is sortable, pagable
- books CRUD
- books categories
- book cover saved to database
- book cover can be imported from PDF (you need to have [ghostScript](https://www.ghostscript.com/))
- themed (JqueryUI)
- i18n
- mysql and sqlite support
- synchronization with filesystem (renames, deletes)
- migrations
- compact: optimizes DB via run vacuum for SQLite or table optimization for MySQL

## Requirements

- PHP 7 (use PHP 5.6 on your own risk, codepage config tweaks are needed)
- sqlite or mysql
- url rewrite (optional if you set **'enablePrettyUrl' => false** in /app/config/config.php)

## Usage

Most js/css comes from CDN networks, so you probably eager to check code at /app/assets/*

### Sync

If you have enabled synchronization application will sync changes to your filesystem
* any cell change that has influence on filename of the book will cause its rename
* if you delete book it will be removed from filesystem too 
* you cannot remove records without corresponding real file (you may disable sync if required)
* you have to manage file extension manually to rename file properly.
* if you change book filename format it will not rename all books! It will apply new format only to newly renamed books

### Importing new books
The simplest way is to drop your books to library folder you've set in configuration and then on "synchronization" press "import fs only". 
It will import all file system books that are not in database yet.

### Configuration
Configuration is pretty straightforward. You need to have filesystem encoding configured if you use sync.
 In PHP7 it look ok for Windows to use UTF8, but before use something like cp1251 codepage.
 
 ## Setup
 
 ### Gist
 
 <pre>composer create-project  yurii-github/yii2-mylib  --stability=dev --no-dev</pre>
 remove --no-dev to get dev required stuff
 
 ### Composer
 
 <pre>composer update</pre>
 
 entry point is located at
 <pre>PROJECT/app/public/index.php</pre>

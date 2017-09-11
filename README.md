
<img src="http://gregfranko.com/images/jqueryui.png"/> 

[![Build Status](https://travis-ci.org/yurii-github/yii2-mylib.svg?branch=master)](https://travis-ci.org/yurii-github/yii2-mylib) [![Code Climate](https://codeclimate.com/github/yurii-github/yii2-mylib/badges/gpa.svg)](https://codeclimate.com/github/yurii-github/yii2-mylib) [![Test Coverage](https://codeclimate.com/github/yurii-github/yii2-mylib/badges/coverage.svg)](https://codeclimate.com/github/yurii-github/yii2-mylib/coverage) [![Yii2](https://img.shields.io/badge/Powered_by-Yii_Framework-green.svg?style=flat)](http://www.yiiframework.com/)

<img src="https://s26.postimg.org/3rs1svuyv/index.png" />

# About

This is book library manager, written in PHP and based on JqueryUI.  
The main purpose of it is to manage own book library as excel table with ratings and other stuff like filename syncronizations of your books and more.  
Said that, it supports all JQuery UI themes.  

Most js/css comes from CDN networks, so you probably eager to check code at /app/assets/*

## Requirements

- php >=7
- sqlite or mysql
- url rewrite (optional if you set **'enablePrettyUrl' => false** in /app/config/config.php)

## Setup

### Gist

<pre>composer create-project  yurii-github/yii2-mylib  --stability=dev --no-dev</pre>
remove --no-dev to get dev required stuff

### Composer

<pre>composer update</pre>

entry point is located at
<pre>PROJECT/app/public/index.php</pre>


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
 

TODO

* authors
* publishers

## Usage

As app is not truly finished, some workarounds must be considered.

### Add New Books
The simplest way is to drop your book(s) to lib folder you've set in configuration and seconds tab "syncronization" press "import fs only". It will import all file system books that are not in database yet.

### Edit Books
You can edit book at frontpage excel sheet.
If you enabled sync, app will require write permissions on your books to rename them accordinly to your book name format (supported tags are limited to {year}, {title}, {publisher}, {author}, {isbn13}, {ext}). Don't forget, that during import app doesn't recognize extension, it adds whole import name into title, so you have to add extension manually to rename file properly.
NOTE! If you change book name format it will not rename all books! App will apply new format only to newly renamed books by you

### Remove Book
To remove book from lib just click trash icon near book.
If you enabled sync, it will remove this book also from file system.

### Configuration

A big note requires filesystem encoding. This is a **MUST** for file sync. In Windows this is mostly cp1251 codepage.


## Console usage

```yii2-console.bat help```  
```yii2-console.bat migrate/history```
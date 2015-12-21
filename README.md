
<div style="background-color:black"><img style="background-color:black" src="https://jqueryui.com/jquery-wp-content/themes/jquery/images/logo-jquery-ui.png"/></div>

[![Build Status](https://travis-ci.org/yurii-github/yii2-mylib.svg?branch=master)](https://travis-ci.org/yurii-github/yii2-mylib) [![Code Climate](https://codeclimate.com/github/yurii-github/yii2-mylib/badges/gpa.svg)](https://codeclimate.com/github/yurii-github/yii2-mylib) [![Test Coverage](https://codeclimate.com/github/yurii-github/yii2-mylib/badges/coverage.svg)](https://codeclimate.com/github/yurii-github/yii2-mylib/coverage) [![Yii2](https://img.shields.io/badge/Powered_by-Yii_Framework-green.svg?style=flat)](http://www.yiiframework.com/)

<b>commits</b>

+ [skip ci]  - skips travis ci builds
+ [sc] - skips sending clover report to codeclimate


<h2>About MyLib</h2>

It is book library manager written in PHP based on JqueryUI for personal usage. Main purpose is to manage own book library as excel table with ratings and other stuff like filename syncronizations of your books and more.
Said that, it supports all JQuery UI themes.

Most js/css come from CDN networks, so you probably eager to check code at /app/assets/*

<img src="http://s16.postimg.org/khmq5yr1x/image.png" />

<img src="http://s8.postimg.org/8j6idmcc5/image.png" />

<h2>Requirements</h2>

+ php >=5.5
+ url rewrite
+ mysqlite or mysql


<h3>Setup</h3>

<pre>composer create-project  yurii-github/yii2-mylib  --stability=dev --no-dev</pre>
remove --no-dev to get dev required stuff

entry point is located at
<pre>PROJECT/app/public/index.php</pre>


<h2>Functionality</h2>

- excel table is sortable, pagable
- books CRUD
- book cover saved to database
- themed (JqueryUI)
- i18n
- mysql and sqlite support
- syncronization with filesystem (renames, deletes)
- migrations
- security (partial)
 
TODO

- admin page. base
- true authors, publishers, categories
- in sqlite run VACUUM for space saving
- CDN fallbacks
- user tests
</pre>


<h2>Usage</h2>

As app is not truly finished, some workarounds must be considered.

<h3>Add New Books</h3>
The simplest way is to drop your book(s) to lib folder you've set in configuration and seconds tab "syncronization" press "import fs only". It will import all file system books that are not in database yet.

<h3>Edit Books</h3>
You can edit book at frontpage excel sheet.
If you enabled sync, app will require write permissions on your books to rename them accordinly to your book name format (supported tags are limited to {year}, {title}, {publisher}, {author}, {isbn13}, {ext}). Don't forget, that during import app doesn't recognize extension, it adds whole import name into title, so you have to add extension manually to rename file properly.
NOTE! If you change book name format it will not rename all books! App will apply new format only to newly renamed books by you

<h3>Remove Book</h3>
To remove book from lib just click trash icon near book.
If you enabled sync, it will remove this book also from file system.

<h3>Configuration</h3>
Email is not functioning. I still think about its value
Security is not implemented for real. low importance
Other things work as expected. A big note requires filesystem encoding. This is a must for file sync. In Windows this is mostly cp1251 codepage.


----------------------

last check true check: 28-10-2015


[![Build Status](https://travis-ci.org/yurii-github/yii2-mylib.svg?branch=master)](https://travis-ci.org/yurii-github/yii2-mylib) [![Code Climate](https://codeclimate.com/github/yurii-github/yii2-mylib/badges/gpa.svg)](https://codeclimate.com/github/yurii-github/yii2-mylib) [![Test Coverage](https://codeclimate.com/github/yurii-github/yii2-mylib/badges/coverage.svg)](https://codeclimate.com/github/yurii-github/yii2-mylib/coverage)

<b>commits</b>

+ [skip ci]  - skips travis ci builds
+ [sc] - skips sending clover report to codeclimate

<h2>WARNING! IT IS VERY RAW</h2>

It is book library manager written in PHP based on JqueryUI for personal usage. Main purpose is to manage own book library as excel table with ratings and other stuff like filename syncronizations of your books and more.

Most js/css come from CDN networks, so you probably eager to check code at /app/assets/*

<img src="http://s24.postimg.org/fhvfecmjp/lib.jpg" />

<img src="http://s29.postimg.org/hdzobbo5z/cfg.jpg" />

<h3>Requirements</h3>

+ php >=5.5
+ url rewrite
+ mysqlite or mysql


<h3>Setup</h3>

<pre>composer create-project  yurii-github/yii2-mylib  --stability=dev --no-dev</pre>
remove --no-dev to get dev required stuff

entry point is located at
<pre>PROJECT/app/public/index.php</pre>


<h3>functionality</h3>

<pre>
(+) implemented | (-) not implemented

+ excel table is sortable, pagable
+ books CRUD
+ book cover saved to database
+ themed
+ i18n
+ mysql and sqlite support
+ syncronization with filesystem
+ migrations

- cache manage
- security and auth. partial
- APC control. partial
- admin page. base
- authors, publishers, categories
- in sqlite run VACUUM for space saving
- - CDN fallbacks
- unit tests
- user tests
</pre>


IIS fix for bootstrap (boostrap currently not used)
<pre><mimeMap fileExtension=".woff2" mimeType="application/font-woff2" /></pre>

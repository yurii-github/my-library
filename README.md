
[![Build Status](https://travis-ci.org/yurii-github/yii2-mylib.svg?branch=master)](https://travis-ci.org/yurii-github/yii2-mylib) [![Code Climate](https://codeclimate.com/github/yurii-github/yii2-mylib/badges/gpa.svg)](https://codeclimate.com/github/yurii-github/yii2-mylib) [![Test Coverage](https://codeclimate.com/github/yurii-github/yii2-mylib/badges/coverage.svg)](https://codeclimate.com/github/yurii-github/yii2-mylib/coverage) [![Yii2](https://img.shields.io/badge/Powered_by-Yii_Framework-green.svg?style=flat)](http://www.yiiframework.com/)

<b>commits</b>

+ [skip ci]  - skips travis ci builds
+ [sc] - skips sending clover report to codeclimate

<h2>About MyLib</h2>

It is book library manager written in PHP based on JqueryUI for personal usage. Main purpose is to manage own book library as excel table with ratings and other stuff like filename syncronizations of your books and more.
Said that, it supports all JQuery UI themes.

Most js/css come from CDN networks, so you probably eager to check code at /app/assets/*

<img src="http://s16.postimg.org/khmq5yr1x/image.png" />

<img src="http://postimg.org/image/5cbytzrw1/" />

<h3>Requirements</h3>

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


IIS fix for bootstrap (boostrap currently not used)
<code>
<mimeMap fileExtension=".woff2" mimeType="application/font-woff2" />
</code>

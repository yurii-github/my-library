It is book library manager written in PHP based on JqueryUI for personal usage. Main purpose is to manage own book library as excel table with ratings and other stuff like filename syncronizations and more.
Most js/css comes from CDN networks, so you probably eager to check code at /app/assets/*

<img src="http://s11.postimg.org/agya5qldf/lib.jpg" />

<img src="http://s29.postimg.org/hdzobbo5z/cfg.jpg" />

<h3>Requirements</h3>

php 5.4+
url rewrite
mysqlite or mysql


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
+ book cover
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

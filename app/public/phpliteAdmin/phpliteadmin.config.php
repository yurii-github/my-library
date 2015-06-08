<?php
$cfg_file = file_get_contents(dirname(dirname(__DIR__)) . '/config/libconfig.json');

$cfg = json_decode($cfg_file);

$password = "";
$directory = false;
$databases = 
[[
	'path' => $cfg->database->filename,
	'name' => 'My Library Db'
]];

$debug = false;
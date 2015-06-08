<?php
namespace app\assets;

class Bootstrap extends AssetBundle
{
	public $sourcePath = null;
	public $depends = ['app\assets\Jquery'];
	
	public $js  = ["https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"];
	public $css = ["https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css"];
}
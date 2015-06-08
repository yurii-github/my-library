<?php
namespace app\assets;

class JqueryRaty extends AssetBundle
{
	public $sourcePath = null;
	public $js = ['jquery-raty/jquery.raty.min.js'];
	public $depends = ['app\assets\Jquery'];
}
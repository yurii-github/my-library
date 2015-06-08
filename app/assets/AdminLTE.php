<?php
namespace app\assets;

class AdminLTE extends AssetBundle
{
	public $sourcePath = '@vendor/almasaeed2010/adminlte/dist';
	public $depends = ['app\assets\Jquery'];
	
	public $publishOptions = ['const-dir' => 'admin-lte'];
	public $css = ['admin-lte/css/AdminLTE.css', 'admin-lte/css/skins/skin-blue.css' ];
	public $js = ['admin-lte/js/app.js'];
}
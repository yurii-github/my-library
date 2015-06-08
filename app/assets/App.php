<?php
namespace app\assets;

class App extends AssetBundle
{
	public $publishOptions = ['const-dir' => 'app'];
	public $css = ['app/css/yui-reset-3.5.0.css', 'app/css/style.css'];
}
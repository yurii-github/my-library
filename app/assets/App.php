<?php
namespace app\assets;

class App extends AssetBundle
{
	public $publishOptions = ['const-dir' => 'app'];
	public $css = ['app/css/yui-reset-3.5.0.css', 'app/css/style.css'];
	
	public $depends = [
	  // GII/YII BUG
	 // 'yii\web\JqueryAsset',
	//  'yii\bootstrap\BootstrapPluginAsset',
	  Jquery::class,
	  JqueryUI::class,
	  JqueryRaty::class,
	  JqueryFancybox::class,
	  JqueryGrid::class
	];
}
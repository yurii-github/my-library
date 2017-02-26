<?php
namespace app\assets;

class App extends AssetBundle
{
	public $publishOptions = ['const-dir' => 'app'];
	public $css = ['app/css/yui-reset-3.5.0.css', 'app/css/style.css'];
	
	public $depends = [
	  //GII/YII BUG: is not loaded in Gii, so we force it to load
	  \yii\web\JqueryAsset::class,
	  \yii\bootstrap\BootstrapAsset::class,
	  \yii\bootstrap\BootstrapPluginAsset::class,
	  \yii\gii\TypeAheadAsset::class,
	  
	  Jquery::class,
	  JqueryUI::class,
	  JqueryRaty::class,
	  JqueryFancybox::class,
	  JqueryGrid::class
	];
}
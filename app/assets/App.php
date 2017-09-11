<?php
namespace app\assets;

class App extends AssetBundle
{
	public $publishOptions = ['const-dir' => 'app'];
	public $css = ['app/css/yui-reset-3.5.0.css', 'app/css/style.css'];
	
	public $depends = [
	  \yii\web\JqueryAsset::class,
	  
	  //GII/YII BUG: asset override is not loaded in Gii, so we force it to load
	  \yii\bootstrap\BootstrapAsset::class,
	  \yii\bootstrap\BootstrapPluginAsset::class,
	  //\yii\gii\TypeAheadAsset::class,
	  
	  JqueryUI::class,
	  JqueryRaty::class,
	  JqueryFancybox::class,
	  JqueryGrid::class
	];
	
	public $js = [ 'js.cookie.js' ];
}
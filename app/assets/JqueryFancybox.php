<?php
namespace app\assets;

class JqueryFancybox extends AssetBundle
{
	public $sourcePath = null;
	public $depends = [\yii\web\JqueryAsset::class];
	
	public $css = ['https://cdnjs.cloudflare.com/ajax/libs/fancybox/2.1.5/jquery.fancybox.min.css'];
	public $js = ['https://cdnjs.cloudflare.com/ajax/libs/fancybox/2.1.5/jquery.fancybox.pack.js'];
}
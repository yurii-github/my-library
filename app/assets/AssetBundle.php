<?php
namespace app\assets;

use yii\web\View;

class AssetBundle extends \yii\web\AssetBundle
{
	//public $sourcePath =  -- DONT USE IF ALREADY MANUALLY PUBLISHED 
	//public $basePath = '@webroot/assets';
	public $baseUrl = '@web/assets';
	public $cssOptions = ['type'=>'text/css'];
	public $jsOptions = ['position' => \yii\web\View::POS_HEAD];
}
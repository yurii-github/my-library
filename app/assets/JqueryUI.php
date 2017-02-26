<?php
namespace app\assets;

class JqueryUI extends AssetBundle
{
	public $sourcePath = null;
	public $depends = [\yii\web\JqueryAsset::class];
	
	public function init()
	{
		$theme = \Yii::$app->mycfg->system->theme;
		$this->js = ["https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"];
		$this->css = ["https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/$theme/jquery-ui.css"];
	
		parent::init();
	}
}
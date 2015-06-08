<?php
namespace app\assets;

class JqueryUI extends AssetBundle
{
	public $sourcePath = null;
	public $depends = ['app\assets\Jquery'];
	
	public function init()
	{
		$theme = \Yii::$app->mycfg->system->theme;
		$this->js = ["https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"];
		$this->css = ["https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/$theme/jquery-ui.css"];
	
		parent::init();
	}
}
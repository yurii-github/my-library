<?php
namespace app\assets;

class JqueryGrid extends AssetBundle
{
	public $sourcePath = null;
	public $depends = ['app\assets\Jquery', 'app\assets\JqueryUI'];
	
	public function init()
	{
		$this->css = ['https://cdnjs.cloudflare.com/ajax/libs/jqgrid/4.6.0/css/ui.jqgrid.css'];
		$this->js = ['https://cdnjs.cloudflare.com/ajax/libs/jqgrid/4.6.0/js/jquery.jqGrid.min.js'];
		
		$supported = [ 'uk-UA' => 'ua' ];//TODO: make check based on files
		if (!empty(@$supported[\Yii::$app->language])) {
			$this->js[] = "https://cdnjs.cloudflare.com/ajax/libs/jqgrid/4.6.0/js/i18n/grid.locale-{$supported[\Yii::$app->language]}.js";
		} else {
			$this->js[] = 'https://cdnjs.cloudflare.com/ajax/libs/jqgrid/4.6.0/js/i18n/grid.locale-en.js';
		}
		
		parent::init();
	}
}
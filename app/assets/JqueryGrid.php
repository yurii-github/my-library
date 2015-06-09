<?php
namespace app\assets;

class JqueryGrid extends AssetBundle
{
	public $sourcePath = null;
	public $depends = ['app\assets\Jquery', 'app\assets\JqueryUI'];
	
	public $css = ['https://cdnjs.cloudflare.com/ajax/libs/jqgrid/4.6.0/css/ui.jqgrid.css'];
	public $js = ['https://cdnjs.cloudflare.com/ajax/libs/jqgrid/4.6.0/js/jquery.jqGrid.min.js'];
	
	public function init()
	{
		$supported = ['uk-UA' => 'ua'];
		
		if (array_key_exists(\Yii::$app->language, $supported)) {
			$this->js[] = "https://cdnjs.cloudflare.com/ajax/libs/jqgrid/4.6.0/js/i18n/grid.locale-{$supported[\Yii::$app->language]}.js";
		} else {
			$this->js[] = 'https://cdnjs.cloudflare.com/ajax/libs/jqgrid/4.6.0/js/i18n/grid.locale-en.js';
		}
		
		parent::init();
	}
}
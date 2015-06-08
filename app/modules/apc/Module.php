<?php
namespace modules\apc;

class Module extends \yii\base\Module
{
	
	public function init()
	{
		parent::init();

		$this->params['foo'] = 'bar';
		// ...  other initialization code ...
	}
}
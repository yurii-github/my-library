<?php
namespace app\components\widgets;

use yii\base\Widget;

class MenuWidget extends Widget
{
	public $items = [];
	
	public function run()
	{
		if (!is_array($this->items)) {
			throw new \Exception('MenuWidget: items must be array');
		}
		
		return $this->render('menu', ['items' => $this->items]);
	}
}
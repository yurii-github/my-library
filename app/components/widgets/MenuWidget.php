<?php
namespace app\components\widgets;

use yii\base\Widget;

class MenuWidget extends Widget
{
	public $items = [];
	
	public function run()
	{
		if (!is_array($this->items)) {
			throw new \yii\base\Exception('MenuWidget: items must be array');
		}
		
		$items = [];
		
		foreach ($this->items as $item) {
		  if (is_array($item)) {
		    $items[] = $item;
		  }
		}
		
		return $this->render('menu', ['items' => $items]);
	}
}
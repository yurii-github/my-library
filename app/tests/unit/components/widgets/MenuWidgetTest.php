<?php
namespace tests\components\widgets;

use \yii\web\View;
use \app\components\widgets\MenuWidget;

class MenuWidgetTest extends \tests\AppTestCase
{
	public function test_run()
	{
		$this->mockYiiApplication();
		
		$mw = new MenuWidget();
		$this->assertContains('id="mylibrary-menu"', $mw->run());
	}
	
	/**
	 * @expectedExceptionMessage MenuWidget: items must be array
	 * @expectedException \yii\base\Exception
	 */
	public function test_badItems()
	{
		$this->mockYiiApplication();
		
		$mw = new MenuWidget(['items' => 'must be array']);
		$mw->run();
		//$this->assertContains('id="mylibrary-menu"', $mw->run());
	}
}
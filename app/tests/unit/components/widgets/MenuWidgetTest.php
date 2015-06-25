<?php
namespace tests\components\widgets;

use \yii\web\View;
use \app\components\widgets\MenuWidget;

class MenuWidgetTest extends \tests\AppTestCase
{
	/**
	 * (non-PHPdoc)
	 * @see PHPUnit_Extensions_Database_TestCase::setUp()
	 */
	protected function setUp()
	{
		parent::setUp();
		$this->mockYiiApplication();
		
	}
	
	
	public function test_run()
	{
		$mw = new MenuWidget();
		$this->assertContains('id="mylibrary-menu"', $mw->run());
	}
	
	
	/**
	 * @expectedExceptionMessage MenuWidget: items must be array
	 * @expectedException \yii\base\Exception
	 */
	public function test_badItems()
	{
		$mw = new MenuWidget(['items' => 'must be array']);
		$mw->run();
	}
	
	
}
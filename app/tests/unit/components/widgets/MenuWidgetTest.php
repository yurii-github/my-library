<?php
namespace tests\components\widgets;

use \app\components\widgets\MenuWidget;

class MenuWidgetTest extends \tests\AppTestCase
{
	protected function setUp(): void
	{
		parent::setUp();
		$this->mockYiiApplication();

	}

	public function test_run()
	{
		$mw = new MenuWidget();
		$this->assertStringContainsString('id="mylibrary-menu"', $mw->run());
	}

	public function test_badItems()
	{
	    $this->expectException(\yii\base\Exception::class);
	    $this->expectExceptionMessage('MenuWidget: items must be array');
		$mw = new MenuWidget(['items' => 'must be array']);
		$mw->run();
	}


}

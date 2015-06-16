<?php
namespace tests\controllers;

use app\controllers\SiteController;

class SiteControllerTest extends \tests\AppTestCase
{
	
	public function test_about()
	{
		$this->mockYiiApplication();
		
		$mockController = $this->getMockBuilder(SiteController::class)
			->setConstructorArgs(['id' => 'site', 'module'=> \Yii::$app])
			->setMethods(['render'])->getMock();
		$mockController->expects($this->once())->method('render')->willReturnCallback(function(){return func_get_args();});
		
		$args = $mockController->actionAbout();
		$this->assertEquals('//about/index', $args[0]);
		$this->assertArraySubset([ 'projects' => ['Yii 2' => 'https://github.com/yiisoft/yii2']], $args[1]);
		$this->assertEquals('About', $mockController->view->title);
	}
	
}
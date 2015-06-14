<?php
namespace tests\components;

use app\components\MyLibraryBootstrap;

class MyLibraryBootstrapTest extends \tests\AppTestCase
{
	public function test_bootstrap()
	{
		$this->mockYiiApplication();
		
		$bootstrap = new MyLibraryBootstrap();
		
		$this->assertEmpty($bootstrap->bootstrap(\Yii::$app));
		//check changes
		$this->assertEquals('session-id', session_name());
		$this->assertEquals(\Yii::$app->mycfg->system->language, \Yii::$app->language);
		$this->assertEquals(\Yii::$app->mycfg->system->timezone, date_default_timezone_get());
	}
	
	
}
<?php
namespace tests\functional;

class PagesTest extends \tests\AppFunctionalTestCase
{

	public function test_Config_IndexPage()
	{
		/* @var $controller \app\controllers\ConfigController */
		$controller = $this->mockController('config');
		$args = $controller->runAction('index');
		$this->assertEquals('index', $args[0]);
	}

	public function test_Site_IndexPage()
	{
		/* @var $controller \app\controllers\SiteController */
		$controller = $this->mockController('site');
		$args = $controller->runAction('index');
		$this->assertEquals('index', $args[0]);
		$this->assertEquals('Books', $controller->view->title);
	}

	public function test_Site_AboutPage()
	{
		/* @var $controller \app\controllers\SiteController */
		$controller = $this->mockController('site');
		$args = $controller->runAction('about');
		
		$this->assertEquals('//about/index', $args[0]);
		$this->assertArraySubset([
			'projects' => [
				'Yii 2' => 'https://github.com/yiisoft/yii2'
			]
		], $args[1]);
		$this->assertEquals('About', $controller->view->title);
	}
	
	
}
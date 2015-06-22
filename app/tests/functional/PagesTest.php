<?php
namespace tests\functional;

class PagesTest extends \tests\AppFunctionalTestCase
{
		
	function test_AboutPage()
	{
		/* @var $controller \app\controllers\SiteController */
		$controller = $this->mockController('site');
		$args = $controller->actionAbout();

		$this->assertEquals('//about/index', $args[0]);
		$this->assertArraySubset([ 'projects' => ['Yii 2' => 'https://github.com/yiisoft/yii2']], $args[1]);
		$this->assertEquals('About', $controller->view->title);
	}

}
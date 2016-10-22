<?php
namespace tests\functional;

class PagesTest extends \tests\AppFunctionalTestCase
{

	public function test_getPermissions()
	{
		try {
			(new MigrationTest())->test_MigrationInstall();
			$controller = $this->mockController('config');
			$resp = $controller->runAction('permissions');
			$view = $resp[0]; $data = $resp[1];
			
			$this->assertEquals('permissions', $view);
			
			$this->assertNotEmpty($data['roles']);
			$this->assertNotEmpty($data['roles']['admins']);
			$this->assertNotEmpty($data['roles']['admins']['edit-books']);
			$this->assertNotEmpty($data['roles']['admins']['list-books']);	
			$this->assertNotEmpty($data['roles']['users']);
			$this->assertNotEmpty($data['roles']['users']['list-books']);
			
			$this->assertNotEmpty($data['perms']);
			$this->assertNotEmpty($data['perms']['edit-books']);
			$this->assertNotEmpty($data['perms']['list-books']);
			
		} finally {
			$this->resetConnection();
		}
	}
	
	
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
				'Yii 2.0.8' => 'https://github.com/yiisoft/yii2',
	            'jQuery' => 'https://jquery.com',
	            'jQuery UI' => 'https://jqueryui.com',
	            'jQuery Grid' => 'http://www.trirand.com/blog',
	            'jQuery Raty' => 'http://wbotelhos.com/raty',
	            'jQuery FancyBox' => 'http://fancybox.net',	
			]
		], $args[1]);
		$this->assertEquals('About', $controller->view->title);
	}
	
	
}
<?php
namespace tests;

class AppFunctionalTestCase extends AppTestCase
{
	protected function setUp()
	{
		parent::setUp();
		
		$this->mockYiiApplication();
	}
	
	
	protected function mockYiiApplication($config = [])
	{
		//work cfg
		$cfg = require $GLOBALS['basedir'] . '/app/config/config.php';
		
		// make work cfg testable
		unset(
			$cfg['id'],
			$cfg['basePath'],
			$cfg['vendorPath'],
			$cfg['components']['db']
		);
		
		return parent::mockYiiApplication(\yii\helpers\ArrayHelper::merge($cfg, $config));
	}
	

	protected function getFixture($name)
	{
		return require self::$baseTestDir ."/data/fixtures/$name.php";
	}
	
	
	/**
	 * mocks render functionality of controller. no more output, just returns arguments [view, data] that render() receives
	 *
	 * @param string $id controller id. examaple "site" will be mocked for "SiteController" accordinly
	 *
	 * @return PHPUnit_Framework_MockObject_MockBuilder
	 */
	protected function mockController($id)
	{
		$class = \Yii::$app->controllerNamespace . '\\' . ucwords($id) . 'Controller';
	
		$mockController = $this->getMockBuilder($class)
		->setConstructorArgs(['id' => $id, 'module'=> \Yii::$app])
		->setMethods(['render'])->getMock();
		$mockController->expects($this->once())->method('render')->willReturnCallback(function(){return func_get_args();});
	
		return $mockController;
	}
	
	
	
}

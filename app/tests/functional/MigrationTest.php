<?php
namespace tests\functional;


class MigrationTest extends \tests\AppFunctionalTestCase
{
	
	protected function mockYiiApplication($config = [])
	{
		$mock_response = $this->getMockBuilder(\yii\web\Response::class)
			//->disableOriginalConstructor()
			->setConstructorArgs([['charset' => 'utf-8']]) //to not trigger app as it is not initted yet
			->setMethods(['redirect'])
			->getMock();
		
		$mock_response->expects($this->once())->method('redirect')->with($this->equalTo(['install/migrate'])); //migration link
		
		$cfg = [
			'components' => [
				'response' => $mock_response 
			]
		];
		
		parent::mockYiiApplication(\yii\helpers\ArrayHelper::merge($cfg, $config));
	}
	
	
	function testMigrationInstall()
	{
		\Yii::$app->mycfg->system->version = 123; // trigger install
		
		/* @var $controller \app\controllers\SiteController */
		$controller = $this->mockController('site');
		$controller->runAction('index');
	}
	
	
}
<?php
namespace tests\functional;


class MigrationTest extends \tests\AppFunctionalTestCase
{
	
	public function test_MigrationInstall()
	{		
		$this->mockYiiApplication([
			'components' => [
				'db' => [
					'pdo' =>  new \PDO("sqlite::memory:")
				]
			]
		]);
		
		/* @var $controller \app\controllers\InstallController */
		$c = $this->mockController('install');
		$r = $c->runAction('migrate');
		$this->assertEquals('//site/migration', $r[0], 'render view does not match');
		$this->assertTrue($r[1]['result'], "migration has failed with content: \n\n". $r[1]['content']);
	}
	
	
	
	
	
	//\Yii::$app->mycfg->system->version = 'wrong-version'; // trigger install
	
	
	
	/*
	
	protected function m2222ockYiiApplication($config = [])
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
	}*/
	
	
	
	
	
}
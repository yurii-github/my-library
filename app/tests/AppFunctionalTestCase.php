<?php
namespace tests;

use \AD7six\Dsn\Dsn;

class AppFunctionalTestCase extends AppTestCase
{
	protected function setUp()
	{
		parent::setUp();
		
		$_SERVER['SERVER_NAME'] = 'phpunit-locahost'; //for yii2 request init. WTF?
		$this->mockYiiApplication();
	}
	
	
	protected function mockYiiApplication($config = [])
	{
		$this->initAppFileSystem();
		//
		// create user's config file, as it will override apps config for database connection in bootstrap
		//TODO: remove duplicates
		$env_db = getenv('DB_TYPE');
		$db = $GLOBALS['db'][$env_db];
		/* @var $mycfg \app\components\Configuration  faking class */
		$mycfg = json_decode(file_get_contents(self::$baseTestDir . '/data/default_config.json'));
		$mycfg->system->version = '1.1'; //to avoid migration install
		$mycfg->database->dbname = @$db['dbname'];
		$mycfg->database->filename = @$db['filename'];
		$mycfg->database->format = $env_db;
		$mycfg->database->host = @$db['host'];
		$mycfg->database->login = @$db['username'];
		$mycfg->database->password = @$db['password'];
		file_put_contents($this->getConfigFilename(), json_encode($mycfg));
		
		//
		//work cfg
		$cfg = require dirname(self::$baseTestDir) . '/config/config.php';
		// make work cfg testable
		unset(
			$cfg['id'],
			$cfg['basePath'],
			$cfg['vendorPath'],
			$cfg['components']['db']
		);

		
		parent::mockYiiApplication(\yii\helpers\ArrayHelper::merge($cfg, $config));
		//var_dump(\Yii::$app->mycfg); die;
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

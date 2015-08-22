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
	
	public function pSupportedDatabases()
	{
		return [
			// format | dsn
			[ 'mysql', 'mysql:host=host X;dbname=dbname X'  ],
			[ 'sqlite', 'sqlite:filename X' ]
		];
	}
	
	/**
	 * 
	 * @dataProvider pSupportedDatabases
	 */
	public function test_DSN($format, $dsn)
	{
		$this->initAppFileSystem();
		
		$cfg = json_decode(file_get_contents(self::$baseTestDir.'/data/default_config.json'));
		$cfg->database->format = $format;
		$cfg->database->host = 'host X';
		$cfg->database->login = 'login X';
		$cfg->database->password = 'password X';
		$cfg->database->dbname = 'dbname X';
		$cfg->database->filename = 'filename X';
		file_put_contents($this->getConfigFilename(), json_encode($cfg));
		
		$this->mockYiiApplication();
		$bootstrap = new MyLibraryBootstrap();
		$bootstrap->bootstrap(\Yii::$app);
		
		$this->assertEquals($dsn, \Yii::$app->db->dsn);	
	}
	
	
	public function test_MigrationEvent()
	{
		try {
			$this->cleanDb();			
			$this->mockYiiApplication();
			\Yii::$app->mycfg->system->version = null; // to trigger migration event
			
			$bootstrap = new MyLibraryBootstrap();
			$bootstrap->bootstrap(\Yii::$app);
			
			$this->assertTrue(\yii\base\Event::hasHandlers(\app\components\Controller::class, \app\components\Controller::EVENT_BEFORE_ACTION),
				'migration event for controller was not added');
		} finally {
			$this->resetConnection();
		}
	}
	
}

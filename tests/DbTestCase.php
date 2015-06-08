<?php
namespace tests;

class DbTestCase extends \PHPUnit_Extensions_Database_TestCase
{
	static $dbc;
	static $pdo;
	protected $dataset = [];
	
	public function getPdo()
	{
		if (empty(self::$pdo)) {
			self::$pdo = new \PDO($GLOBALS['dsn'], $GLOBALS['login'], $GLOBALS['password'], [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);
			self::$pdo->query(file_get_contents(__DIR__.'/data/db.sqlite.txt')); //init tables
		}
		
		return self::$pdo;
	} 

	public function getConnection()
	{
		if (empty(self::$dbc)) {
			self::$dbc = $this->createDefaultDBConnection($this->getPdo());
		}
		return self::$dbc;
	}

	public function getDataSet()
	{
		return $this->createArrayDataSet($this->dataset);
	}
	
	
	protected function tearDown()
	{
		parent::tearDown();
		$this->destroyApplication();
	}
		
	
	protected function mockYiiApplication($config = [])
	{
		new \yii\web\Application(\yii\helpers\ArrayHelper::merge([
			'id' => 'testapp',
			'basePath' => __DIR__,
			'vendorPath' => dirname(__DIR__) . '/vendor',
			'components' => [
				'db' => (new \yii\db\Connection(['pdo' => $this->getPdo()])),
				'request' => [
					'cookieValidationKey' => 'key',
					'scriptFile' => __DIR__ .'/index.php',
					'scriptUrl' => '/index.php',
				],
			]
		], $config));
	}

	
	protected function destroyApplication()
	{
		\Yii::$app = null;
	}
	
	
}
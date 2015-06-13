<?php
namespace tests;

use app\components\Configuration;
use org\bovigo\vfs\vfsStream;

class AppTestCase extends \PHPUnit_Extensions_Database_TestCase
{
	static $dbc;
	static $pdo;
	protected $dataset = [ //for clearing
		'books' => [],
		'users' => []
	];
	
	private $is_fs_init = false;

	public function getPdo()
	{
		if (empty(self::$pdo)) {
			self::$pdo = new \PDO('sqlite::memory:', null, null, [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);
			
			//init tables
			foreach (explode(';', file_get_contents(__DIR__.'/data/db.sqlite.txt')) as $query) {
				self::$pdo->query($query);
			}
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
		
		$this->destroyApplication();
		parent::tearDown();
		
	}
	
	protected function mockYiiApplication($config = [])
	{
		$this->initAppFileSystem();
		
		new \yii\web\Application(\yii\helpers\ArrayHelper::merge([
			'id' => 'testapp',
			'basePath' => $GLOBALS['basedir'].'/app',
			'vendorPath' => $GLOBALS['basedir'] . '/vendor',
			'aliases' => [
				'@app' => vfsStream::url('base'),
				'@runtime' => '@app/runtime'
			],
			'components' => [
				'security' => [
					'passwordHashStrategy' => 'password_hash',
				],
				'db' => (new \yii\db\Connection(['pdo' => $this->getPdo()])),
				'request' => [
					'cookieValidationKey' => 'key',
					'scriptFile' => __DIR__ .'/index.php',
					'scriptUrl' => '/index.php',
				],
				'mycfg' => new Configuration(['config_file' => '@app/config/libconfig.json' ])
			]
		], $config));

	}

	
	protected function destroyApplication()
	{
		\Yii::$app = null;
	}
	
	

	// - - - - - - FS - - - - >
	protected function getConfigFilename()
	{
		return \Yii::getAlias('@app/config/libconfig.json');
	}
	
	protected function getBaseFileSystem()
	{
		$this->initAppFileSystem();
		
		return vfsStream::url('base');
	}
	
	protected function initAppFileSystem()
	{
		if ($this->is_fs_init) {
			return;
		}
	
		vfsStream::setup('base', null, [
			'config' => [], 
			'data' => [
				'books' => []
			],
			'runtime' => [
				'logs' => []
			],
			'public' => [
				'assets' => []
			]
		]);
		
		\Yii::$aliases['@app'] = vfsStream::url('base');
		//\Yii::$aliases['@webroot'] = vfsStream::url('base/public');
		
		$this->is_fs_init = true;
	}
	
	// < - - - - - - FS - - - - -
	
	
	
}
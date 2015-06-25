<?php
namespace tests {

use app\components\Configuration;
use org\bovigo\vfs\vfsStream;

class AppTestCase extends \PHPUnit_Extensions_Database_TestCase
{
	static $baseTestDir = __DIR__;
	static $pdo;	// pdo connection
	static $dbc;	// dbunit connection, contains pdo
	
	protected $dataset = [ //for clearing with dbunit via truncate
		'books' => [],
		'users' => []
	];
	
	private $is_fs_init = false;

	
	/**
	 * 
	 * @return \PDO
	 */
	public function getPdo()
	{
		if (empty(self::$pdo)) {
			$env_db = getenv('DB_TYPE');
			$db = $GLOBALS['db'][$env_db];
			self::$pdo = new \PDO($db['dsn'], @$db['username'], @$db['password'], [ \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION ]);
			
			//init tables
			foreach (explode(';', file_get_contents(self::$baseTestDir."/data/db.$env_db.txt")) as $query) {
				if (!empty($query)) {
					self::$pdo->query($query);
				}
			}
		}
		
		return self::$pdo;
	}
	
	
	/**
	 * (non-PHPdoc)
	 * @see PHPUnit_Extensions_Database_TestCase::getConnection()
	 */
	public function getConnection()
	{
		if (empty(self::$dbc)) {
			self::$dbc = $this->createDefaultDBConnection($this->getPdo());
		}
		return self::$dbc;
	}

	
	/**
	 * (non-PHPdoc)
	 * @see PHPUnit_Extensions_Database_TestCase::getDataSet()
	 */
	public function getDataSet()
	{
		return $this->createArrayDataSet($this->dataset);
	}
	
	
	/**
	 * (non-PHPdoc)
	 * @see PHPUnit_Extensions_Database_TestCase::tearDown()
	 */
	protected function tearDown()
	{
		$this->destroyApplication();
		parent::tearDown();
		
	}
	
	
	/**
	 * 
	 * @param unknown $config
	 */
	protected function mockYiiApplication($config = [])
	{
		$this->initAppFileSystem();
		
		$env_db = getenv('DB_TYPE');
		$db = $GLOBALS['db'][$env_db];

		new \yii\web\Application(\yii\helpers\ArrayHelper::merge([
			'id' => 'testapp',
			'basePath' => vfsStream::url('base'),
			'vendorPath' => dirname(dirname(self::$baseTestDir)) . '/vendor',
			'aliases' => [
				'@runtime' => '@app/runtime'
			],
			'components' => [				
				//'basePath' => \Yii::getAlias('@app/public/assets')
				'i18n' => [
					'translations' => [
						'frontend/*' => [
							'class' => \yii\i18n\PhpMessageSource::class,
							'basePath' => $GLOBALS['basedir'] .'/i18n',
							'sourceLanguage' => 'en-US'
						]
					],
				],
				'security' => [
					'class' => \app\components\Security::class,
				],
				'db' => [
					'class' => \yii\db\Connection::class,
					'dsn' => $db['dsn'],
					'username' => @$db['username'],
					'password' => @$db['password'],
					'pdo' => $this->getPdo()
				],
				'request' => [
					'cookieValidationKey' => 'key',
					'scriptFile' => __DIR__ .'/index.php',
					'scriptUrl' => '/index.php',
				],
				'mycfg' => [
					'class' => \app\components\Configuration::class,
					'config_file' => $this->getConfigFilename()
				]
			]
		], $config));

	}

	
	/**
	 * 
	 */
	protected function destroyApplication()
	{
		\Yii::$app = null;
	}
	
	
	/**
	 * 
	 * @param unknown $name
	 */
	private function getFixture($name)
	{
		return require self::$baseTestDir ."/data/fixtures/$name.php";
	}
	
	
	/**
	 * 
	 * @param string $name table/fixture name
	 * @return unknown
	 */
	protected function setupFixture($name)
	{
		$fixture = $this->getFixture($name);
		
		$this->dataset = [
			$name => $fixture['insert']
		];
		
		
		return $fixture;
	}
	

	// - - - - - - FS - - - - >
	protected function getConfigFilename()
	{
		return \Yii::getAlias('@app/config/libconfig.json');
	}
	
	protected function initAppFileSystem()
	{
		if ($this->is_fs_init) {
			return vfsStream::url('base');
		}
	
		vfsStream::setup('base', null, [
			'config' => [], 
			'data' => [
				'books' => []
			],
			'emails' => [
				'layouts' => [],
				'notification' => []
			],
			'runtime' => [
				'logs' => [],
				'mail' => []
			],
			'public' => [
				'assets' => []
			]
		]);
		
		\Yii::$aliases['@app'] = vfsStream::url('base');
		//\Yii::$aliases['@webroot'] = vfsStream::url('base/public');
		
		$this->is_fs_init = true;
		
		return vfsStream::url('base');
	}
	
	// < - - - - - - FS - - - - -
	
	
	
}

}

namespace yii\base {
	//vsFS fix in MOdule
	function realpath($path)
	{
		return $path;
	}
}
<?php
namespace tests {

use app\components\Configuration;
use org\bovigo\vfs\vfsStream;

class AppTestCase extends \PHPUnit_Extensions_Database_TestCase
{
	static $baseTestDir = __DIR__;
	
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
			$env_db = getenv('DB_TYPE');
			$db = $GLOBALS['db'][$env_db];

			self::$pdo = new \PDO($db['dsn'], $db['username'], $db['password'], [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);
			
			//init tables
			foreach (explode(';', file_get_contents(self::$baseTestDir."/data/db.$env_db.txt")) as $query) {
				if (!empty($query)) {
					self::$pdo->query($query);
				}
				
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
			'basePath' => vfsStream::url('base'),
			'vendorPath' => $GLOBALS['basedir'] . '/vendor',
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
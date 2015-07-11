<?php
namespace app\components
{
	use yii\base\Object;
	use yii\helpers\Json;
	use app\components\configuration\System;
	use app\components\configuration\Library;
	use app\components\configuration\Database;
	use app\components\configuration\Book;
						
	/**
	 * @property string $version
	 * @property \app\components\configuration\System $system
	 * @property \app\components\configuration\Library $library
	 * @property \app\components\configuration\Database $database
	 * @property \app\components\configuration\Book $book
	 *        
	 */
	class Configuration extends Object
	{
		private $version = '1.1';
		private $config;
		public $config_file;
		
		private $options = ['system', 'database', 'library', 'book'];

		public function __construct($config = [])
		{
			$config['config_file'] = \Yii::getAlias($config['config_file']);
			parent::__construct($config);
			
			if (!file_exists($this->config_file)) {
				$this->saveDefaultCfg();
			} else {
				$this->load($this->config_file);
			}
			
		}
		
		/**
		 * (non-PHPdoc)
		 * @see \yii\base\Object::__get()
		 */
		public function __get($name)
		{
			if (in_array($name, $this->options)) {
				return $this->config->$name;
			}
				
			return parent::__get($name);
		}
		

		/**
		 * 
		 * @return string
		 */
		public function getVersion()
		{
			return $this->version;
		}
		
		protected function saveDefaultCfg()
		{
			$this->config = $this->getDefaultCfg();
			$this->save();
		}

		
		public function load($filename)
		{
			if (!is_readable($filename)) {
				throw new \yii\base\InvalidValueException('cannot read config file at this location: '.$filename);
			}
			
			$this->config = Json::decode(file_get_contents($filename), false);
			
			\Yii::beginProfile('reflection', 'config');
			//
			// silently injects newly introduced option into current config from default config
			//
			$def_config = $this->getDefaultCfg();
			$rf1 = new \ReflectionObject($def_config);
			/* @var $p_base \ReflectionProperty */
			foreach ($rf1->getProperties() as $p_base) {// lvl-1: system, book ...
				$lvl1 = $p_base->name;
				if (empty($this->config->$lvl1)) {
					$this->config->$lvl1 = $def_config->$lvl1;
					continue;
				}
				$rf2 = new \ReflectionObject($def_config->{$p_base->name});
				foreach ($rf2->getProperties() as $p_option) {//lvl-2: system->theme ..
					$lvl2 = $p_option->name;
					if (empty($this->config->$lvl1->$lvl2)) {
						$this->config->$lvl1->$lvl2 = $def_config->$lvl1->$lvl2;
						continue;//reserved. required for lvl-3 if introduced
					}
				}
			}
			\Yii::endProfile('reflection', 'config');
		}
		
	
		protected function getDefaultCfg()
		{
			$directory = addslashes(\Yii::getAlias('@app/data/books/')); //TODO: must end with slash. need to force it on update
			$filename = addslashes(\Yii::getAlias('@app/data/mydb.s3db'));
			$json = 
			<<<JSON
{
    "system": {
        "email": false,
        "emailto": null,
        "theme": "smoothness",
        "timezone": "Europe\/Kiev",
        "language": "en-US"
    },
    "library": {
        "codepage": "cp1251",
        "directory": "$directory",
        "sync": false
    },
    "database": {
        "format": "sqlite",
        "filename": "$filename",
        "host": "localhost",
        "dbname": "mylib",
        "login": "",
        "password": ""
    },
    "book": {
        "covermaxwidth": 800,
        "covertype": "image\/jpeg",
        "nameformat": "{year}, ''{title}'', {publisher} [{isbn13}].{ext}"
    }
}
JSON;
			return json_decode($json);
		}

		
		/**
		 * gets encoded utf-8 string in filesystem codepage type
		 * @param string $filename
		 * @return string
		 */
		public function Encode($filename)
		{
			return mb_convert_encoding($filename, $this->library->codepage, 'utf-8');
		}
		
		/**
		 * gets utf-8 string decoded from filesystem codepage type
		 * @param unknown $filename
		 * @return string
		 */
		public function Decode($filename)
		{
			return mb_convert_encoding($filename, 'utf-8', $this->library->codepage);
		}

		
		public function save()
		{
			$filename = $this->config_file;
			$config_dir = dirname($this->config_file);

			if (file_exists($filename) && !is_writable($filename)) {
				throw new \yii\base\InvalidValueException("file '$filename' is not writable", 1);
			} elseif (is_dir($config_dir) && !is_writable($config_dir)) {
				throw new \yii\base\InvalidValueException("config directory '$config_dir' is not writable", 2);
			} elseif (!is_dir($config_dir)) {
				throw new \yii\base\InvalidValueException("config directory '$config_dir' does not exist", 3);
			}
			 
			file_put_contents($filename, Json::encode($this->config, JSON_PRETTY_PRINT));			
		}

	}
}

//
// config type hints support
//
namespace app\components\configuration
{
	/**
	 * @property bool $email send email of notification on actions
	 * @property string $emailto address of meial to send notification if enabled
	 * @property string $theme
	 * @property string $timezone
	 * @property string $language   
	 */
	class System {}
	
	/**
	 * @property string $codepage
	 * @property string $directory
	 * @property string $sync
	 */
	class Library {}
	
	/**
	 * @property string $filename
	 * @property string $dbname
	 * @property string $format
	 * @property string $host
	 * @property string $login
	 * @property string $password
	 */
	class Database {}

	/**    
	 * @property string $nameformat
	 * @property string $covertype
	 * @property int $covermaxwidth
	 */
	class Book {}
}



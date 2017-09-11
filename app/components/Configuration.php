<?php
/*
 * My Book Library
 *
 * Copyright (C) 2014-2017 Yurii K.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see http://www.gnu.org/licenses
 */

namespace app\components
{
	use yii\base\Object;
	use yii\helpers\Json;
	use \yii\base\InvalidValueException;
	use app\components\configuration\System;
	use app\components\configuration\Library;
	use app\components\configuration\Database;
	use app\components\configuration\Book;
						
	/**
	 * @property string $version
	 * @property System $system
	 * @property Library $library
	 * @property Database $database
	 * @property Book $book
	 *        
	 */
	class Configuration extends Object
	{
		// NOTE: only supported in PHP 5.6+
		const SUPPORTED_VALUES = [
			'system_language' => [
				'en-US' => 'English - en-US',
				'uk-UA' => 'Українська - uk-UA'
			],
			'system_theme' => [ // known list of JqueryUI themes
				'base',
				'black-tie',
				'blitzer',
				'cupertino',
				'dark-hive',
				'dot-luv',
				'eggplant',
				'excite-bike',
				'flick',
				'hot-sneaks',
				'humanity',
				'le-frog',
				'mint-choc',
				'overcast',
				'pepper-grinder',
				'redmond',
				'smoothness',
				'south-street',
				'start',
				'sunny',
				'swanky-purse',
				'trontastic',
				'ui-darkness',
				'ui-lightness',
				'vader'
			],
			'system_timezone' => [
				// based on system support of DateTimeZone::listIdentifiers() 
			]
			
		];

        private $version = '1.3'; // DB VERSION

		//TODO: change version here too!
		const DEFAULT_CONFIG_JSON = <<<JSON
{
    "system": {
        "version": "1.3",
        "theme": "smoothness",
        "timezone": "Europe\/Kiev",
        "language": "en-US"
    },
    "library": {
        "codepage": "cp1251",
        "directory": "%app_directory%\/data\/books\/",
        "sync": false
    },
    "database": {
        "format": "sqlite",
        "filename": "%app_directory%\/data\/mydb.s3db",
        "host": "localhost",
        "dbname": "mylib",
        "login": "",
        "password": ""
    },
    "book": {
        "covermaxwidth": 800,
        "covertype": "image\/jpeg",
        "nameformat": "{year}, ''{title}'', {publisher} [{isbn13}].{ext}",
        "ghostscript": ""
    }
}
JSON;

        public $config_file;
		private $config;
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
				throw new InvalidValueException('cannot read config file at this location: '.$filename);
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
		
		
		/**
		 * returns default configuration as php object
		 * @return mixed
		 */
		protected function getDefaultCfg()
		{
			return json_decode(str_replace('%app_directory%', addslashes(\Yii::getAlias('@app')), self::DEFAULT_CONFIG_JSON));
		}

		
		/**
		 * gets encoded utf-8 string in filesystem codepage type
		 * @param string $filename
		 * @return string
		 */
		public function Encode($filename)
		{
		  if (PHP_MAJOR_VERSION >= 7) {
		    return $filename;
		  }
		  
			return mb_convert_encoding($filename, $this->library->codepage, 'utf-8');
		}
		
		/**
		 * gets utf-8 string decoded from filesystem codepage type
         *
		 * @param string $filename
		 * @return string
		 */
		public function Decode($filename)
		{
		  if (PHP_MAJOR_VERSION >= 7) {
		    return $filename;
		  }
		  
			return mb_convert_encoding($filename, 'utf-8', $this->library->codepage);
		}

		
		public function save()
		{
			$filename = $this->config_file;
			$config_dir = dirname($this->config_file);

			if (file_exists($filename) && !is_writable($filename)) {
				throw new InvalidValueException("file '$filename' is not writable", 1);
			} elseif (is_dir($config_dir) && !is_writable($config_dir)) {
				throw new InvalidValueException("config directory '$config_dir' is not writable", 2);
			} elseif (!is_dir($config_dir)) {
				throw new InvalidValueException("config directory '$config_dir' does not exist", 3);
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
	 * @property string $theme
	 * @property string $timezone
	 * @property string $language
	 * @property string $version this param is only set after successful migration install  
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
     * @property  string ghostscript
	 */
	class Book {}
}



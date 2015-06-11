<?php
namespace tests\components;

use app\components\Configuration;
use org\bovigo\vfs\vfsStream;
use yii\db\ActiveRecord;
use app\components\configuration\Library;
use app\components\configuration\Database;

class mockConfiguration extends Configuration
{
	public function getDefaultCfg()
	{
		return parent::getDefaultCfg();
	}
}

class ConfigurationTest extends \tests\AppTestCase
{
	/* @var $config Configuration */
	private $config;

	protected function setUp()
	{
		$this->initAppFileSystem();
		$this->config = new mockConfiguration(['config_file' => $this->getConfigFilename()]);
	}
	
	
	public function test_gets()
	{
		$this->assertTrue(is_string($this->config->getVersion()));
		$this->assertInstanceOf(Library::class, $this->config->library);
		$this->assertInstanceOf(Database::class, $this->config->database);
	}
	
	
	public function test_save()
	{
		$this->config->save();
		$this->assertTrue(file_exists($this->getConfigFilename()), 'config file was not saved');
		
		/* @var $default Configuration */
		/* @var $saved Configuration */
		$default = json_decode(file_get_contents($GLOBALS['basedir'].'/app/tests/data/default_config.json'));
		$saved = json_decode(file_get_contents($this->getConfigFilename()));
		$this->assertEquals($default, $saved, 'saved config file doesnt match default one');
		
		//check changes are saved
		$this->config->system->language = 'yo-yo';
		$this->config->save();
		$changed = json_decode(file_get_contents($this->getConfigFilename()));
		$this->assertEquals($this->config->system->language, 'yo-yo', 'config object was not changed');
		$this->assertEquals($this->config->system->language, $changed->system->language, 'config change was not saved to file');
	}
	
	// test introduction of new option into default config, via reflection
	public function test_load()
	{
		// set clean config file, must load defaults from default config
		file_put_contents($this->config->config_file, '{}'); //empty json

		$def_config = $this->config->getDefaultCfg(); // cfg will be used for loading as default
		$def_config->system->level2 = 'value 2'; // test new level 2. must be added
		
		$mock_cfg = $this->getMockBuilder('\app\components\Configuration')->disableOriginalConstructor()->setMethods(['getDefaultCfg'])->getMock();
		$mock_cfg->expects($this->any())->method('getDefaultCfg')->willReturn($def_config);
		$mock_cfg->load($this->config->config_file); // load old config, our modified default config must apply level1 and level2 params
		
		$this->assertInstanceOf(Library::class, $mock_cfg->library);
		$this->assertEquals($mock_cfg->system->level2, 'value 2');
	}
	
	
	public function test_EncodeDecode()
	{
		$filename = 'фівзїхыssAsd.ext'; //utf-8
		$enc = $this->config->Encode($filename); // set codepage
		$dec = $this->config->Decode($enc); // utf-8
		
		$this->assertEquals($filename, $dec, 'filename encode/decode has failed');
	}
	

	/**
	 * @expectedException yii\base\InvalidCallException
	 */
	function test_setVersion_NotAllowed()
	{
		$this->config->version = 'asd';
	}
	
	/**
	 * @expectedException yii\base\InvalidCallException
	 */
	function test_setSystem_NotAllowed()
	{
		$this->config->system = 'asd';
	}
	
	
	/**
	 * @expectedException yii\base\InvalidValueException
	 */
	function test_load_WrongConfigFile()
	{
		$this->config->load('asd/asd/asd');
	}
	
	/**
	 * @expectedException yii\base\InvalidValueException
	 */
	function test_save_WrongConfigFile()
	{
		$this->config->config_file = 'asd/asd/asd';
		$this->config->save();
	}
	
	
	
	
	
	
	
	
	

}
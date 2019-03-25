<?php

namespace tests\components;

use app\components\Configuration;
use org\bovigo\vfs\vfsStream;
use yii\db\ActiveRecord;
use app\components\configuration\Library;
use app\components\configuration\Database;

class mockConfiguration extends Configuration
{
    public function getDefaultConfiguration()
    {
        return parent::getDefaultConfiguration();
    }
}


class ConfigurationTest extends \tests\AppTestCase
{
    /**
     * configuration mock object
     * @var Configuration
     */
    private $config;

    /**
     * not real configuration, just json decode of default configuration file
     * @var Configuration
     */
    private $def_cfg;


    /**
     * (non-PHPdoc)
     * @see PHPUnit_Extensions_Database_TestCase::setUp()
     */
    protected function setUp(): void
    {
        $this->initAppFileSystem();
        $this->config = new mockConfiguration(['version' => '1.3', 'config_file' => $this->getConfigFilename()]);
        $this->def_cfg = json_decode(file_get_contents(self::$baseTestDir . '/data/default_config.json'));
    }


    public function test_gets()
    {
        $this->assertTrue(is_string($this->config->getVersion()));
        $this->assertEquals($this->def_cfg->library->directory, $this->config->library->directory);
        $this->assertEquals($this->def_cfg->database->filename, $this->config->database->filename);
    }


    public function test_save()
    {
        $this->config->save();
        $this->assertTrue(file_exists($this->getConfigFilename()), 'config file was not saved');

        /* @var $saved Configuration */
        $saved = json_decode(file_get_contents($this->getConfigFilename()));
        $this->assertEquals($this->def_cfg, $saved, 'saved config file doesnt match default one');

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

        $def_config = $this->config->getDefaultConfiguration(); // cfg will be used for loading as default
        $def_config->system->level2 = 'value 2'; // test new level 2. must be added

        $mock_cfg = $this->getMockBuilder('\app\components\Configuration')->disableOriginalConstructor()->setMethods(['getDefaultCfg'])->getMock();
        $mock_cfg->expects($this->any())->method('getDefaultCfg')->willReturn($def_config);
        $mock_cfg->load($this->config->config_file); // load old config, our modified default config must apply level1 and level2 params

        $this->assertEquals('cp1251', $mock_cfg->library->codepage);
    }


    public function test_EncodeDecode()
    {
        $filename = 'фівзїхыssAsd.ext'; //utf-8
        $enc = $this->config->Encode($filename); // set codepage
        $dec = $this->config->Decode($enc); // utf-8

        $this->assertEquals($filename, $dec, 'filename encode/decode has failed');
    }


    function test_load_WrongConfigFile()
    {
        $this->expectException(\yii\base\InvalidValueException::class);
        $this->config->load('asd/asd/asd');
    }

    function test_save_WrongConfigFile()
    {
        $this->expectException(\yii\base\Exception::class);
        $this->config->config_file = 'asd/asd/asd';
        $this->config->save();
    }

    function test_save_DirectoryIsNotWritable()
    {
        $this->expectException(\yii\base\InvalidValueException::class);
        $this->expectExceptionCode(2);
        unlink($this->getConfigFilename());
        chmod(dirname($this->getConfigFilename()), 0444);
        $this->config->save();
    }

    function test_getNotExistedProperty()
    {
        $this->expectException(\yii\base\UnknownPropertyException::class);
        $c = $this->config->not_exist;
    }

    function test_setNotExistedProperty()
    {
        $this->expectException(\yii\base\UnknownPropertyException::class);
        $this->config->not_exist = 'value';
    }

    function test_setNotExistedProperty_ForNotExisted2ndLevel()
    {
        $this->expectException(\yii\base\UnknownPropertyException::class);
        $this->config->not_exist->asd = 'value';
    }


    function test_setNotExistedProperty_ForExisted2ndLevel()
    {
        $this->config->system->asd = 'value';
        $this->assertEquals('value', $this->config->system->asd);
    }
}

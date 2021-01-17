<?php

namespace Tests;

use \App\Configuration\Configuration;
use App\Exception\ConfigFileIsNotWritableException;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;

use \App\Exception\ConfigurationPropertyDoesNotExistException;

//class mockConfiguration extends Configuration
//{
//    public function getDefaultConfiguration()
//    {
//        return parent::getDefaultConfiguration();
//    }
//}

class ConfigurationTest extends TestCase
{
    /** @var vfsStreamDirectory|null */
    protected static $fs = null;
    /**  @var Configuration loaded from default config file */
    private $configLoaded; // config
    /** @var \stdClass not real configuration, just json decode of default configuration file */
    private $configDecoded; // def_cfg

    protected function setUp(): void
    {
        $this->initVirtualFileSystem();
        defined('DATA_DIR') || define('DATA_DIR', vfsStream::url('base/data'));
        $vfsConfigFile = vfsStream::url('base/data/config.json');
        $configData = file_get_contents(dirname(__DIR__) . '/data/config_sqlite.json');
        file_put_contents($vfsConfigFile, $configData);
        $this->configLoaded = new Configuration($vfsConfigFile, '1.3');
        $this->configDecoded = json_decode($configData);
    }


    function test_getNotExistedProperty()
    {
        $this->expectException(ConfigurationPropertyDoesNotExistException::class);
        $this->expectExceptionMessage("Property 'not_exist' does not exist");
        $this->configLoaded->not_exist;
    }


//    function test_setNotExistedProperty()
//    {
//        $this->expectException(ConfigurationPropertyDoesNotExistException::class);
//        $this->configLoaded->not_exist = 'value';
//    }
//    
//    function test_setNotExistedProperty_ForNotExisted2ndLevel()
//    {
//        $this->expectException(ConfigurationPropertyDoesNotExistException::class);
//        $this->configLoaded->not_exist->asd = 'value';
//    }
//
//
//    function __test_setNotExistedProperty_ForExisted2ndLevel()
//    {
//        $this->configLoaded->system->asd = 'value';
//        $this->assertEquals('value', $this->configLoaded->system->asd);
//    }
    
    
    public function testGetters()
    {
        $this->assertIsString($this->configLoaded->getVersion());
        $this->assertEquals($this->configDecoded->library->directory, $this->configLoaded->library->directory);
        $this->assertEquals($this->configDecoded->database->filename, $this->configLoaded->database->filename);
    }


    function testDirectoryIsNotWritable()
    {
        $this->expectException(ConfigFileIsNotWritableException::class);
        $this->expectExceptionCode(2);
        unlink($this->configLoaded->config_file);
        chmod(dirname($this->configLoaded->config_file), 0444);
        $this->configLoaded->save();
    }


    public function testSaveCorrectJson()
    {
        $this->configLoaded->save();
        $saved = json_decode(file_get_contents($this->configLoaded->config_file));
        $this->assertEquals($this->configDecoded, $saved, 'saved config file doesnt match default one');
    }


    public function testSaveChanges()
    {
        $this->configLoaded->save();
        $this->configLoaded->system->language = 'yo-yo';
        $this->assertEquals($this->configLoaded->system->language, 'yo-yo', 'config object was not changed');
        $this->configLoaded->save();

        $saved = json_decode(file_get_contents($this->configLoaded->config_file));
        $this->assertEquals($this->configLoaded->system->language, $saved->system->language, 'config change was not saved to file');
    }


    protected function initVirtualFileSystem()
    {
        self::$fs = vfsStream::setup('base', null, [
            'data' => [
                'books' => [],
                'logs' => [],
            ],
        ]);
    }
}
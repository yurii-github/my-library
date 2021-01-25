<?php

namespace Tests;

use \App\Configuration\Configuration;
use App\Exception\ConfigurationDirectoryDoesNotExistException;
use App\Exception\ConfigurationDirectoryIsNotWritableException;
use App\Exception\ConfigurationFileIsNotReadableException;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use \App\Exception\ConfigurationPropertyDoesNotExistException;

class ConfigurationTest extends TestCase
{
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

    public function testPopulateNewProperties()
    {
        $this->assertTrue(property_exists($this->configDecoded->library, 'sync'));
        unset($this->configDecoded->library->sync);
        file_put_contents(vfsStream::url('base/data/config.json'), json_encode($this->configDecoded));
        $this->assertFalse(property_exists($this->configDecoded->library, 'sync'));
        $config = new Configuration(vfsStream::url('base/data/config.json'), '1.3');

        $this->assertTrue(property_exists($config->library, 'sync'));
        $this->assertIsBool($config->library->sync);
        $this->assertFalse($config->library->sync);
        
        $loaded = json_decode(file_get_contents(vfsStream::url('base/data/config.json')), false);
        $this->assertFalse(property_exists($loaded->library, 'sync'));

        $config->save();
        $loaded = json_decode(file_get_contents(vfsStream::url('base/data/config.json')), false);
        $this->assertTrue(property_exists($loaded->library, 'sync'));
        $this->assertIsBool($loaded->library->sync);
        $this->assertFalse($loaded->library->sync);
    }
    
    
    public function testConfigurationSetupFromDefaults()
    {
        $newFilename = vfsStream::url('base/data/config_new.json');
        $this->assertFileNotExists($newFilename);

        $config = new Configuration($newFilename, '1.3');

        $this->assertFileExists($newFilename);
        $this->assertSame('1.3', $config->getVersion());
        $this->assertStringStartsWith(vfsStream::url('base/data'), $config->getLibrary()->directory);
        $this->assertStringStartsWith(vfsStream::url('base/data'), $config->getDatabase()->filename);
    }

    public function testCannotSaveConfigurationToNonExistingDirectory()
    {
        vfsStream::setup('base');
        $this->assertDirectoryNotExists(vfsStream::url('base/data'));

        $this->expectException(ConfigurationDirectoryDoesNotExistException::class);
        $this->expectExceptionMessage("Directory 'vfs://base/data' does not exist");
        $config = new Configuration(vfsStream::url('base/data/config.json'), '1.3');
    }

    public function testConfigurationFileIsNotReadable()
    {
        $this->expectException(ConfigurationFileIsNotReadableException::class);
        $this->expectExceptionMessage("Cannot read configuration from file 'vfs://base/data/config_new.json'");

        $newFilename = vfsStream::url('base/data/config_new.json');
        file_put_contents($newFilename, '');
        chmod($newFilename, 0000);
        $this->assertNotIsReadable($newFilename);

        $config = new Configuration($newFilename, '1.3');
    }

    function testCannotGetNotExistedProperty()
    {
        $this->expectException(ConfigurationPropertyDoesNotExistException::class);
        $this->expectExceptionMessage("Property 'not_exist' does not exist");
        $this->configLoaded->not_exist;
    }


    function testCannotSetNotExistedProperty()
    {
        $this->expectException(ConfigurationPropertyDoesNotExistException::class);
        $this->configLoaded->not_exist = 'value';
    }

    function testCannotSetNotExistedPropertyForNotExistedPropertyOfSecondLevel()
    {
        $this->expectException(ConfigurationPropertyDoesNotExistException::class);
        $this->configLoaded->not_exist->asd = 'value';
    }


    function testCanSetNotExistedPropertyForExistedPropertyOfSecondLevel()
    {
        $this->configLoaded->system->asd = 'value';
        $this->assertEquals('value', $this->configLoaded->system->asd);
    }


    public function testGetters()
    {
        $this->assertIsString($this->configLoaded->getVersion());
        $this->assertEquals($this->configDecoded->library->directory, $this->configLoaded->library->directory);
        $this->assertEquals($this->configDecoded->database->filename, $this->configLoaded->database->filename);
    }


    function testConfigurationDirectoryIsNotWritable()
    {
        $this->expectException(ConfigurationDirectoryIsNotWritableException::class);
        $this->expectExceptionMessage("Directory 'vfs://base/data' is not writable");
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
        vfsStream::setup('base', null, [
            'data' => [
                'books' => [],
                'logs' => [],
            ],
        ]);
    }
}
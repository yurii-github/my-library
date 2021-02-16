<?php

namespace Tests\Unit;

use \App\Configuration\Configuration;
use App\Exception\ConfigurationDirectoryDoesNotExistException;
use App\Exception\ConfigurationDirectoryIsNotWritableException;
use App\Exception\ConfigurationFileIsNotReadableException;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use \App\Exception\ConfigurationPropertyDoesNotExistException;

class ConfigurationTest extends TestCase
{
    protected function setUp(): void
    {
        vfsStream::setup('base/data');
        defined('DATA_DIR') || define('DATA_DIR', vfsStream::url('base/data'));
    }

    /**
     * @param Configuration $configLoaded loaded from default config file
     * @param \stdClass $configDecoded not real configuration, just json decode of default configuration file
     * @throws ConfigurationDirectoryDoesNotExistException
     * @throws ConfigurationDirectoryIsNotWritableException
     * @throws ConfigurationFileIsNotReadableException
     * @throws \App\Exception\ConfigurationFileIsNotWritableException
     * @return array
     */
    protected function setupGenericCheck(&$configLoaded, &$configDecoded): array
    {
        $vfsConfigFile = vfsStream::url('base/data/config.json');
        $configData = file_get_contents(dirname(__DIR__) . '/data/config_sqlite.json');
        file_put_contents($vfsConfigFile, $configData);
        $configLoaded = new Configuration($vfsConfigFile, '1.3');
        $configDecoded = json_decode($configData);
        
        return [$configLoaded, $configDecoded];
    }

    
    public function testPopulateNewProperties()
    {
        $configDecoded = json_decode(file_get_contents(dirname(__DIR__) . '/data/config_sqlite.json'));
        
        $this->assertTrue(property_exists($configDecoded->library, 'sync'));
        unset($configDecoded->library->sync);
        file_put_contents(vfsStream::url('base/data/config.json'), json_encode($configDecoded));
        $this->assertFalse(property_exists($configDecoded->library, 'sync'));
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
    
    public function testSetVirtualProperty()
    {
        $this->setupGenericCheck($configLoaded, $configDecoded);
        $this->assertFalse($configLoaded->library->sync);

        $configLoaded->library->sync = true;
        $this->assertTrue($configLoaded->library->sync);
    }

    public function testSetNewVirtualGroupFromDefaults()
    {
        $configFilename = vfsStream::url('base/data/config.json');
        $configDecoded = json_decode(file_get_contents(dirname(__DIR__) . '/data/config_sqlite.json'));
        $this->assertTrue(property_exists($configDecoded, 'library'));
        
        unset($configDecoded->library);
        $this->assertFalse(property_exists($configDecoded, 'library'));

        file_put_contents($configFilename, json_encode($configDecoded));

        $config = new Configuration($configFilename, '1.3');

        $this->assertInstanceOf(\stdClass::class, $config->library);
        $this->assertFalse( $config->library->sync);
    }
    
    
    public function testConfigurationSetupFromDefaults()
    {
        $newFilename = vfsStream::url('base/data/config_new.json');
        $this->assertFileDoesNotExist($newFilename);

        $config = new Configuration($newFilename, '1.3');

        $this->assertFileExists($newFilename);
        $this->assertSame('1.3', $config->getVersion());
        $this->assertStringStartsWith(vfsStream::url('base/data'), $config->getLibrary()->directory);
        $this->assertStringStartsWith(vfsStream::url('base/data'), $config->getDatabase()->filename);
    }

    public function testCannotSaveConfigurationToNonExistingDirectory()
    {
        vfsStream::setup('base');
        $this->assertDirectoryDoesNotExist(vfsStream::url('base/data'));

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
        $this->assertIsNotReadable($newFilename);

        $config = new Configuration($newFilename, '1.3');
    }

    function testCannotGetNotExistedProperty()
    {
        $this->setupGenericCheck($configLoaded, $configDecoded);
        $this->expectException(ConfigurationPropertyDoesNotExistException::class);
        $this->expectExceptionMessage("Property 'not_exist' does not exist");
        $configLoaded->not_exist;
    }


    function testCannotSetNotExistedProperty()
    {
        $this->setupGenericCheck($configLoaded, $configDecoded);
        $this->expectException(ConfigurationPropertyDoesNotExistException::class);
        $configLoaded->not_exist = 'value';
    }

    function testCannotSetNotExistedPropertyForNotExistedPropertyOfSecondLevel()
    {
        $this->setupGenericCheck($configLoaded, $configDecoded);
        $this->expectException(ConfigurationPropertyDoesNotExistException::class);
        $configLoaded->not_exist->asd = 'value';
    }


    function testCanSetNotExistedPropertyForExistedPropertyOfSecondLevel()
    {
        $this->setupGenericCheck($configLoaded, $configDecoded);
        $configLoaded->system->asd = 'value';
        $this->assertEquals('value', $configLoaded->system->asd);
    }


    public function testGetters()
    {
        $this->setupGenericCheck($configLoaded, $configDecoded);
        $this->assertIsString($configLoaded->getVersion());
        $this->assertEquals($configDecoded->library->directory, $configLoaded->library->directory);
        $this->assertEquals($configDecoded->database->filename, $configLoaded->database->filename);
    }


    function testConfigurationDirectoryIsNotWritable()
    {
        $this->expectException(ConfigurationDirectoryIsNotWritableException::class);
        $this->expectExceptionMessage("Directory 'vfs://base/data' is not writable");

        $this->setupGenericCheck($configLoaded, $configDecoded);
        assert($configLoaded instanceof Configuration);
        unlink($configLoaded->getConfigFile());
        chmod(dirname($configLoaded->getConfigFile()), 0444);
        $configLoaded->save();
    }


    public function testSaveCorrectJson()
    {
        $this->setupGenericCheck($configLoaded, $configDecoded);
        assert($configLoaded instanceof Configuration);
        $configLoaded->save();
        $saved = json_decode(file_get_contents($configLoaded->getConfigFile()));
        $this->assertEquals($configDecoded, $saved, 'saved config file doesnt match default one');
    }


    public function testSaveChanges()
    {
        $this->setupGenericCheck($configLoaded, $configDecoded);
        assert($configLoaded instanceof Configuration);
        $configLoaded->save();
        $configLoaded->system->language = 'yo-yo';
        $this->assertEquals($configLoaded->system->language, 'yo-yo', 'config object was not changed');
        $configLoaded->save();

        $saved = json_decode(file_get_contents($configLoaded->getConfigFile()));
        $this->assertEquals($configLoaded->system->language, $saved->system->language, 'config change was not saved to file');
    }

}
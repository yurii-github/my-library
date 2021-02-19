<?php

namespace Tests\Functional\Api\Config;

use Illuminate\Support\Str;
use Tests\Functional\AbstractTestCase;

class UpdateActionTest extends AbstractTestCase
{
    public function testSave()
    {
        $config = $this->getLibraryConfig();
        $this->assertSame('en-US', $config->getSystem()->language);
        $this->assertStringContainsString('"language": "en-US",', file_get_contents($config->getConfigFile()));
        $this->assertStringNotContainsString('"language": "uk-UA",', file_get_contents($config->getConfigFile()));

        $request = $this->createJsonRequest('POST', '/api/config');
        $request = $request->withParsedBody([
            'field' => 'system_language',
            'value' => 'uk-UA'
        ]);
        $response = $this->app->handle($request);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('', (string)$response->getBody());
        $this->assertStringContainsString('"language": "uk-UA",', file_get_contents($config->getConfigFile()));
        $this->assertStringNotContainsString('"language": "en-US",', file_get_contents($config->getConfigFile()));
    }

    public function test_LibraryDirectory_MustEndWithSlash()
    {
        $config = $this->getLibraryConfig();
        $this->assertStringEndsWith('/', $config->getLibrary()->directory);
        $directoryWithoutSlash = Str::replaceLast('/', '', $config->getLibrary()->directory);
        $this->assertStringEndsNotWith('/', $directoryWithoutSlash);

        $request = $this->createJsonRequest('POST', '/api/config');
        $request = $request->withParsedBody([
            'field' => 'library_directory',
            'value' => $directoryWithoutSlash
        ]);
        $response = $this->app->handle($request);

        $this->assertSame(500, $response->getStatusCode());
        $this->assertJsonError('Library directory must end with a slash!', 0, 'InvalidArgumentException', $response);
    }

    public function test_LibraryDirectory_DirectoryMustExist()
    {
        $config = $this->getLibraryConfig();
        $this->assertDirectoryExists($config->getLibrary()->directory);
        $invalidDirectory = $config->getLibrary()->directory . 'unknown-dir/';
        $this->assertDirectoryDoesNotExist($invalidDirectory);

        $request = $this->createJsonRequest('POST', '/api/config');
        $request = $request->withParsedBody([
            'field' => 'library_directory',
            'value' => $invalidDirectory
        ]);
        $response = $this->app->handle($request);

        $this->assertSame(500, $response->getStatusCode());
        $this->assertJsonError('Library directory must exist!', 0, 'InvalidArgumentException', $response);
    }

    public function test_LibraryDirectory_DirectoryMustBeReadable()
    {
        $directory = $this->getLibraryConfig()->getLibrary()->directory;
        $this->assertDirectoryExists($directory);
        $this->assertDirectoryIsReadable($directory);
        chmod($directory, 0000);
        $this->assertDirectoryIsNotReadable($directory);

        $request = $this->createJsonRequest('POST', '/api/config');
        $request = $request->withParsedBody([
            'field' => 'library_directory',
            'value' => $directory
        ]);
        $response = $this->app->handle($request);

        $this->assertSame(500, $response->getStatusCode());
        $this->assertJsonError('Library directory must be readable!', 0, 'InvalidArgumentException', $response);
    }

    public function testCannotSaveWithoutWriteAccess()
    {
        $config = $this->getLibraryConfig();
        $this->assertSame('en-US', $config->getSystem()->language);
        $this->assertStringContainsString('"language": "en-US",', file_get_contents($config->getConfigFile()));
        $this->assertStringNotContainsString('"language": "uk-UA",', file_get_contents($config->getConfigFile()));

        chmod($config->getConfigFile(), 0444);

        $request = $this->createJsonRequest('POST', '/api/config');
        $request = $request->withParsedBody([
            'field' => 'system_language',
            'value' => 'uk-UA'
        ]);
        $response = $this->app->handle($request);

        $this->assertSame(500, $response->getStatusCode());
        $this->assertJsonError("File 'vfs://base/data/config.json' is not writable", 0, 'App\Exception\ConfigurationFileIsNotWritableException', $response);
    }
}
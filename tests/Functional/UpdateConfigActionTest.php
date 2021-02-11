<?php

namespace Tests\Functional;

class UpdateConfigActionTest extends AbstractTestCase
{
    public function testSave()
    {
        $config = $this->getLibraryConfig();
        $this->assertSame('en-US', $config->getSystem()->language);
        $this->assertStringContainsString('"language": "en-US",', file_get_contents($config->getConfigFile()));
        $this->assertStringNotContainsString('"language": "uk-UA",', file_get_contents($config->getConfigFile()));

        $request = $this->createJsonRequest('POST', '/config/save');
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


    public function testCannotSaveWithoutWriteAccess()
    {
        $config = $this->getLibraryConfig();
        $this->assertSame('en-US', $config->getSystem()->language);
        $this->assertStringContainsString('"language": "en-US",', file_get_contents($config->getConfigFile()));
        $this->assertStringNotContainsString('"language": "uk-UA",', file_get_contents($config->getConfigFile()));

        chmod($config->getConfigFile(), 0444);

        $request = $this->createJsonRequest('POST', '/config/save');
        $request = $request->withParsedBody([
            'field' => 'system_language',
            'value' => 'uk-UA'
        ]);
        $response = $this->app->handle($request);

        $this->assertSame(500, $response->getStatusCode());
        $this->assertJsonError("File 'vfs://base/data/config.json' is not writable", 0, 'App\Exception\ConfigurationFileIsNotWritableException', $response);
    }
}
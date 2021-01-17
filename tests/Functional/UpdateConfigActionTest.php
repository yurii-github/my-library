<?php

namespace Tests\Functional;

use App\Configuration\Configuration;

class UpdateConfigActionTest extends AbstractTestCase
{
    public function testSave()
    {
        $config = $this->app->getContainer()->get(Configuration::class);
        assert($config instanceof Configuration);
        $this->assertSame('en-US', $config->getSystem()->language);
        $this->assertStringContainsString('"language": "en-US",', file_get_contents($config->config_file));
        $this->assertStringNotContainsString('"language": "uk-UA",', file_get_contents($config->config_file));

        $request = $this->createJsonRequest('POST', '/config/save');
        $request = $request->withParsedBody([
            'field' => 'system_language',
            'value' => 'uk-UA'
        ]);
        $response = $this->app->handle($request);
        $c = (string)$response->getBody();
        $this->assertSame(200, $response->getStatusCode());
        $this->assertJsonData([
            'msg' => '<b>language</b> was successfully updated',
            'result' => true,
            'title' => 'system'
        ], $response);

        $this->assertStringContainsString('"language": "uk-UA",', file_get_contents($config->config_file));
        $this->assertStringNotContainsString('"language": "en-US",', file_get_contents($config->config_file));
    }
}
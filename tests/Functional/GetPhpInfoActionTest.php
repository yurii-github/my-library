<?php

namespace Tests\Functional;

class GetPhpInfoActionTest extends AbstractTestCase
{
    public function testGetPhpInfo()
    {
        $request = $this->createRequest('GET', '/config/php-info');
        $response = $this->app->handle($request);
        
        $this->assertSame(200, $response->getStatusCode());
        $this->assertNotEmpty((string)$response->getBody());
    }
}
<?php

namespace Tests\Functional;

use Tests\PopulateBooksTrait;

class ConfigCompactDatabaseActionTest extends AbstractTestCase
{
    use PopulateBooksTrait;

    public function testAction()
    {
        $this->populateBooks();

        $request = $this->createRequest('POST', '/config/compact-database');
        $response = $this->app->handle($request);
        
        $this->assertSame(200, $response->getStatusCode());
        $this->assertNotEmpty((string)$response->getBody());
    }
}
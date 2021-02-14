<?php

namespace Tests\Functional\Api\Book;

use Tests\Functional\AbstractTestCase;
use Tests\PopulateBooksTrait;

class ManageActionTest extends AbstractTestCase
{
    use PopulateBooksTrait;

    public function testUnsupportedOperationThrowsException()
    {
        $request = $this->createJsonRequest('POST', '/api/book/manage');
        $response = $this->app->handle($request);

        $this->assertSame(400, $response->getStatusCode());
        $this->assertJsonError("Operation '' is not supported!", 0, "App\\Exception\\UnsupportedOperationException", $response);
    }

}
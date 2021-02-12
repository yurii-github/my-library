<?php

namespace Tests\Functional\Api\Config;

use Tests\Functional\AbstractTestCase;
use Tests\PopulateBooksTrait;

class CountBooksWithoutFilesActionTest extends AbstractTestCase
{
    use PopulateBooksTrait;

    public function testAction()
    {
        $this->setBookLibrarySync(false);
        $books = $this->populateBooks();
        file_put_contents($this->getLibraryConfig()->getFilepath($books[0]->file->getFilename()), 'test data');

        $request = $this->createRequest('GET', '/api/config/count-books-without-files');
        $response = $this->app->handle($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertEquals(2, (string)$response->getBody(), 'db only records count is wrong');
        $this->assertDatabaseCount('books', 3);
    }
}
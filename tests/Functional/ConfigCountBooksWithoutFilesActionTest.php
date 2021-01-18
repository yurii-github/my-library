<?php

namespace Tests\Functional;

use Tests\PopulateBooksTrait;

class ConfigCountBooksWithoutFilesActionTest extends AbstractTestCase
{
    use PopulateBooksTrait;

    public function testAction()
    {
        $this->setBookLibrarySync(false);
        $books = $this->populateBooks();
        file_put_contents($this->getLibraryConfig()->getFilepath($books[0]->filename), 'test data');

        $request = $this->createRequest('GET', '/config/count-books-without-files');
        $response = $this->app->handle($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertEquals(2, (string)$response->getBody(), 'db only records count is wrong');
        $this->assertDatabaseCount('books', 3);
    }
}
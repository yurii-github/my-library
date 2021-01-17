<?php

namespace Tests\Functional;

use Tests\PopulateBooksTrait;

class ConfigClearDbFilesActionTest extends AbstractTestCase
{
    use PopulateBooksTrait;


    public function testCountRecords()
    {
        $this->setBookLibrarySync(false);
        $books = $this->populateBooks();
        file_put_contents($this->getLibraryConfig()->getFilepath($books[0]->filename), 'test data');
        
        $request = $this->createRequest('GET', '/config/clear-db-files');
        $request = $request->withQueryParams(['count' => 'all']);
        $response = $this->app->handle($request);
        
        $this->assertSame(200, $response->getStatusCode());
        $this->assertEquals(2, (string)$response->getBody(), 'db only records count is wrong');
        $this->assertDatabaseCount('books', 3);
    }


    public function testClearDatabaseFromBooksWithoutFiles()
    {
        $this->setBookLibrarySync(false);
        $books = $this->populateBooks();
        $book = $books[0];
        file_put_contents($this->getLibraryConfig()->getFilepath($book->filename), 'test data');

        $this->assertDatabaseCount('books', 3);
        
        $request = $this->createRequest('GET', '/config/clear-db-files');
        $response = $this->app->handle($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertEqualsCanonicalizing(
            [$books[1]->book_guid, $books[2]->book_guid],
            json_decode((string)$response->getBody(), true)
        );
        $this->assertDatabaseCount('books', 1);
        $this->assertDatabaseHas('books', [
            'book_guid' => $book->book_guid,
            'title' => $book->title,
        ]);
    }
    
    
}
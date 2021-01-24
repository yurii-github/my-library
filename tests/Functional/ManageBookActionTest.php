<?php

namespace Tests\Functional;

use Illuminate\Support\Carbon;
use Tests\PopulateBooksTrait;

class ManageBookActionTest extends AbstractTestCase
{
    use PopulateBooksTrait;

    public function testUnsupportedOperationThrowsException()
    {
        $request = $this->createJsonRequest('POST', '/api/book/manage');
        $response = $this->app->handle($request);

        $content = (string)$response->getBody();
        $this->assertSame(500, $response->getStatusCode());
        $this->assertStringContainsString('Unsupported operation!', $content);
    }
    
    public function testAddBook_CannotAddBookWithoutFileWithSync()
    {
        $this->setBookLibrarySync(true);
        Carbon::setTestNow(Carbon::now());
        $request = $this->createJsonRequest('POST', '/api/book/manage', [
            'title' => 'title book #1',
            'read' => 'no',
            'favorite' => 0,
            'oper' => 'add'
        ]);

        $response = $this->app->handle($request);

        $this->assertSame(400, $response->getStatusCode());
        $this->assertJsonData([
            'error' => "Book 'vfs://base/data/books/, ''title book #1'',  [].' does not exist."
        ], $response);
        $this->assertDatabaseCount('books', 0);
    }
    
    
    public function testAddBook_Successful()
    {
        $this->setBookLibrarySync(false);
        Carbon::setTestNow(Carbon::now());
        $request = $this->createJsonRequest('POST', '/api/book/manage', [
            'title' => 'title book #1',
            'read' => 'no',
            'favorite' => 0,
            'oper' => 'add'
        ]);

        $response = $this->app->handle($request);

        $this->assertSame(200, $response->getStatusCode());
        $content = (string)$response->getBody();
        $this->assertNotEmpty($content);
        $content = json_decode($content, true);
        $this->assertArrayHasKey('book_guid', $content);
        $this->assertDatabaseCount('books', 1);
        $this->assertDatabaseHas('books', [
            'book_guid' => $content['book_guid'],
            'title' => 'title book #1',
            'created_date' => Carbon::now()->toDateTimeString(),
            'updated_date' => Carbon::now()->toDateTimeString(),
            'book_cover' => null,
            'favorite' => 0,
            'read' => 'no',
            'year' => null,
            'isbn13' => null,
            'author' => null,
            'publisher' => null,
            'ext' => null,
            'filename' => ", ''title book #1'',  []."
        ]);
        $this->assertTrue(
            (bool)preg_match('/[A-Z0-9]{8}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{12}/',
                $content['book_guid']),
            "book_guid '{$content['book_guid']}' is in wrong format"
        );
    }

    public function testDeleteBook_Successful()
    {
        $this->setBookLibrarySync(false); //TODO: remove file if sync is ON
        $books = $this->populateBooks();
        $book = $books[0];

        $request = $this->createJsonRequest('POST', '/api/book/manage', [
            'id' => $book->book_guid,
            'oper' => 'del'
        ]);
        $response = $this->app->handle($request);
        $this->assertSame('', (string)$response->getBody());

        $this->assertDatabaseCount('books', 2);
        $this->assertDatabaseMissing('books', ['book_guid' => $book->book_guid]);
    }

    public function testEditBook_ChangeFilenameIsSkipped()
    {
        $createdAt = Carbon::now();
        $updatedAt = Carbon::now()->copy()->addDay();
        Carbon::setTestNow($createdAt);
        $this->setBookLibrarySync(false);
        $books = $this->populateBooks();
        $book = $books[0];

        Carbon::setTestNow($updatedAt);
        $request = $this->createJsonRequest('POST', '/api/book/manage', [
            'id' => $book->book_guid,
            'filename' => 'some-new-filename.pdf',
            'oper' => 'edit'
        ]);
        $response = $this->app->handle($request);

        $content = (string)$response->getBody();
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('', $content);
        $this->assertDatabaseHas('books', [
            'book_guid' => $book->book_guid,
            'title' => $book->title,
            'filename' => $book->filename,
            'created_date' => $createdAt->toDateTimeString(),
            'updated_date' => $createdAt->toDateTimeString()
        ]);
    }

    public function testEditBook_BookRecordMustExist()
    {
        $this->assertDatabaseCount('books', 0);
        
        $request = $this->createJsonRequest('POST', '/api/book/manage', [
            'id' => 'unknown-id',
            'filename' => 'some-new-filename.pdf',
            'oper' => 'edit'
        ]);
        $response = $this->app->handle($request);

        $content = (string)$response->getBody();
        $this->assertSame(422, $response->getStatusCode());
        $this->assertJsonData(['id' => ['validation.exists']], $response);
        $this->assertDatabaseCount('books', 0);
    }

    public function testEditBook_CanChangeFilenameFromTitleWithoutFileWithoutSync()
    {
        $this->setBookLibrarySync(false); // TODO: rename file if sync is ON
        $books = $this->populateBooks();
        $book = $books[0];

        $request = $this->createJsonRequest('POST', '/api/book/manage', [
            'id' => $book->book_guid,
            'title' => 'new title X',
            'oper' => 'edit'
        ]);

        $response = $this->app->handle($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('', (string)$response->getBody());
        $this->assertDatabaseHas('books', [
            'book_guid' => $book->book_guid,
            'title' => 'new title X',
            'filename' => ", ''new title X'',  []."
        ]);
    }


    public function testEditBook_CannotChangeFilenameFromTitleWithoutFileWithSync()
    {
        $this->setBookLibrarySync(false);
        $books = $this->populateBooks();
        $book = $books[0];
        $this->setBookLibrarySync(true);

        $request = $this->createJsonRequest('POST', '/api/book/manage', [
            'id' => $book->book_guid,
            'title' => 'new title X',
            'oper' => 'edit'
        ]);

        $response = $this->app->handle($request);
        $this->assertSame(400, $response->getStatusCode());
        $this->assertJsonData([
            'error' => "Sync for file failed. Source file 'vfs://base/data/books/filename-1' does not exist"
        ], $response);
        $this->assertDatabaseHas('books', [
            'book_guid' => $book->book_guid,
            'title' => $book->title,
            'filename' => $book->filename
        ]);
    }


    public function testEditBook_CannotChangeFilenameFromTitleWithFileWithSync()
    {
        $this->setBookLibrarySync(false);
        $books = $this->populateBooks();
        $book = $books[0];
        $config = $this->getLibraryConfig();

        file_put_contents($config->getFilepath($book->filename), 'sample-data');
        $this->assertFileExists($config->getFilepath($book->filename));
        $this->assertStringEqualsFile($config->getFilepath($book->filename), 'sample-data');

        $this->setBookLibrarySync(true);

        $request = $this->createJsonRequest('POST', '/api/book/manage', [
            'id' => $book->book_guid,
            'title' => 'new title X',
            'oper' => 'edit'
        ]);

        $response = $this->app->handle($request);
        $newFilename = ", ''new title X'',  [].";
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('', (string)$response->getBody());
        $this->assertDatabaseHas('books', [
            'book_guid' => $book->book_guid,
            'title' => 'new title X',
            'filename' => $newFilename
        ]);
        // book file was moved
        $this->assertFileNotExists($config->getFilepath($book->filename));
        $this->assertFileExists($config->getFilepath($newFilename));
        $this->assertStringEqualsFile($config->getFilepath($newFilename), 'sample-data');
    }

}
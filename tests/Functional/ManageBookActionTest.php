<?php

namespace Tests\Functional;

use Illuminate\Support\Carbon;
use Tests\PopulateBooksTrait;

class ManageBookActionTest extends AbstractTestCase
{
    use PopulateBooksTrait;


    public function testAddBook()
    {
        Carbon::setTestNow(Carbon::now());
        $request = $this->createJsonRequest('POST', '/api/book/manage', [
            'title' => 'title book #1',
            'read' => 'no',
            'favorite' => 0,
            'oper' => 'add'
        ]);
        $response = $this->app->handle($request);
        $response->getBody()->rewind();
        
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
            'filename' => ", ''title book #1'',  []." // TODO: read format from config
        ]);
        $this->assertTrue(
            (bool)preg_match('/[A-Z0-9]{8}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{12}/',
                $content['book_guid']),
            "book_guid '{$content['book_guid']}' is in wrong format"
        );
    }


    /**
     * MUST
     * 1. remove record from books table based on book_guid
     * 2. TODO: remove file if sync is ON
     */
    public function test_action_Manage_Delete()
    {
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

}
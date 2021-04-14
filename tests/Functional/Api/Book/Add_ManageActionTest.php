<?php

namespace Tests\Functional\Api\Book;

use Illuminate\Support\Carbon;
use Tests\Functional\AbstractTestCase;
use Tests\PopulateBooksTrait;

class Add_ManageActionTest extends AbstractTestCase
{
    use PopulateBooksTrait;

    public function testCannotAddBookWithoutFileWithSync()
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

        $this->assertSame(500, $response->getStatusCode());
        $this->assertJsonError("Book 'vfs://base/data/books/, ''title book #1'',  [].' does not exist.", 0, "App\\Exception\\BookFileNotFoundException", $response);
        $this->assertDatabaseCount('books', 0);
    }


    public function testSuccessful()
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
            'filename' => ", ''title book #1'',  []."
        ]);
        $this->assertTrue(
            (bool)preg_match('/[A-Z0-9]{8}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{12}/',
                $content['book_guid']),
            "book_guid '{$content['book_guid']}' is in wrong format"
        );
    }
}
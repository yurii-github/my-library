<?php

namespace Tests\Functional\Api\Book\Cover;

use Tests\Functional\AbstractTestCase;
use Tests\PopulateBooksTrait;

class GetActionTest extends AbstractTestCase
{
    use PopulateBooksTrait;


    function testEmptyCoverHasDefaultImage()
    {
        $books = $this->populateBooks();
        $request = $this->createRequest('GET', "/api/book/cover/{$books[0]->book_guid}");
        $response = $this->app->handle($request);
        $response->getBody()->rewind();
        $content = $response->getBody()->getContents();

        $cover = file_get_contents(WEB_DIR . '/assets/app/book-cover-empty.jpg');
        $this->assertSame(200, $response->getStatusCode());
        $this->assertEquals($cover, $content);
    }

    function testBookCoverIsReturned()
    {
        $books = $this->populateBooks();
        $book = $books[0];
        $book->book_cover = 'new-cover-data';
        $book->saveOrFail();
        $request = $this->createRequest('GET', "/api/book/cover/{$book->book_guid}");
        $response = $this->app->handle($request);
        $response->getBody()->rewind();
        $content = $response->getBody()->getContents();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertEquals('new-cover-data', $content);
    }
}
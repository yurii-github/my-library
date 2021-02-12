<?php

namespace Tests\Functional\Api\Cover;

use App\Models\Book;
use GuzzleHttp\Psr7\Stream;
use Tests\Functional\AbstractTestCase;
use Tests\PopulateBooksTrait;

class UpdateCoverActionTest extends AbstractTestCase
{
    use PopulateBooksTrait;

    
    public function testCoverResize()
    {
        $books = $this->populateBooks();
        
        $cover = file_get_contents(self::getTestFilepath('cover.jpg'));
        $request = $this->createJsonRequest('POST', '/api/book/cover-save');
        $request = $request->withQueryParams([
            'book_guid' => $books[0]->book_guid
        ]);
        $stream = new Stream(fopen('php://temp', 'r+'));
        $stream->write($cover);
        $request = $request->withBody($stream);
        $response = $this->app->handle($request);
        $this->assertSame(200, $response->getStatusCode());
        $content = json_decode((string)$response->getBody(), true);
        $this->assertNull($content);
        
        /** @var Book $book */
        $book = Book::query()->findOrFail($books[0]->book_guid);

        $this->assertNotNull($book->book_cover);
        $this->assertLessThan(strlen($cover), strlen($book->book_cover), 'resized image is not smaller than original');
        $this->assertNotFalse(imagecreatefromstring($book->book_cover));
    }


    public function testInvalidCover()
    {
        $books = $this->populateBooks();
        $request = $this->createJsonRequest('POST', '/api/book/cover-save');
        $request = $request->withQueryParams([
            'book_guid' => $books[0]->book_guid
        ]);
        $stream = new Stream(fopen('php://temp', 'r+'));
        $stream->write('invalid-cover-fomatted-data');
        $stream->rewind();
        $request = $request->withBody($stream);
        $response = $this->app->handle($request);
        $response->getBody()->rewind();
        $content = $response->getBody()->getContents();
        
        $this->assertSame(422, $response->getStatusCode());
        $this->assertJsonError("",0,'App\Exception\InvalidImageException', $response);
    }

}
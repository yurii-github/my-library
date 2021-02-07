<?php

namespace Tests\Functional;

use App\Models\Book;
use App\Models\BookFile;
use Illuminate\Support\Collection;
use Tests\PopulateBooksTrait;

class ConfigGetBooksWithoutCoverActionTest extends AbstractTestCase
{
    use PopulateBooksTrait;

    public function testWithoutPdfExtensionAndWithoutCover()
    {
        $this->populateBooks();

        $request = $this->createRequest('GET', '/config/books-without-cover');
        $response = $this->app->handle($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertJsonData([], $response);
    }

    public function testWithoutPdfExtensionAndWithCover()
    {
        $books = $this->populateBooks();
        foreach ($books as $book) {
            $book->book_cover = 'some-data';
            $book->save();
        }

        $request = $this->createRequest('GET', '/config/books-without-cover');
        $response = $this->app->handle($request);
        $content = (string)$response->getBody();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertJsonData([], $response);
    }

    public function testWithPdfExtensionAndWithCover()
    {
        $books = $this->populateBooks();
        foreach ($books as $book) {
            $book->file = new BookFile($book->file->filename .'.pdf');
            $book->book_cover = 'some-data';
            $book->save();
        }

        $request = $this->createRequest('GET', '/config/books-without-cover');
        $response = $this->app->handle($request);
        $content = (string)$response->getBody();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertJsonData([], $response);
    }

    public function testWithPdfExtensionAndWithoutCover()
    {
        $books = $this->populateBooks();
        foreach ($books as $book) {
            $book->file = new BookFile($book->file->filename .'.pdf');
            $book->book_cover = null;
            $book->save();
        }
        $arrayBooks = Collection::make($books)->map(function (Book $book) {
            return ['filename' => $book->file->filename, 'book_guid' => $book->book_guid];
        })->toArray();

        $request = $this->createRequest('GET', '/config/books-without-cover');
        $response = $this->app->handle($request);
        $content = (string)$response->getBody();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($arrayBooks, json_decode($content, true));
    }

    public function testWithPdfExtensionAndOneWithCover()
    {
        $books = $this->populateBooks();
        foreach ($books as $book) {
            $book->file = new BookFile($book->file->filename .'.pdf');
            $book->book_cover = null;
            $book->save();
        }
        $bookWithCover = $books[1];
        $bookWithCover->book_cover = 'some-data';
        $bookWithCover->save();

        $arrayBooks = Collection::make($books)->filter(function (Book $book) use ($bookWithCover) {
            return $book->book_guid !== $bookWithCover->book_guid;
        })->map(function (Book $book) {
            return ['filename' => $book->file->filename, 'book_guid' => $book->book_guid];
        })->toArray();

        $request = $this->createRequest('GET', '/config/books-without-cover');
        $response = $this->app->handle($request);
        $content = (string)$response->getBody();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($arrayBooks, json_decode($content, true));
    }
}
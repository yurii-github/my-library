<?php declare(strict_types=1);

namespace Tests\Functional\Api\Config;

use App\Actions\Api\Config\GetBooksWithoutCoverAction;
use App\Models\Book;
use App\Models\BookFile;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\Functional\AbstractTestCase;
use Tests\PopulateBooksTrait;

#[CoversClass(GetBooksWithoutCoverAction::class)]
#[CoversClass(Book::class)]
class GetBooksWithoutCoverActionTest extends AbstractTestCase
{
    use PopulateBooksTrait;

    public function testWithoutPdfExtensionAndWithoutCover()
    {
        $this->populateBooks();

        $request = $this->createRequest('GET', '/api/config/books-without-cover');
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

        $request = $this->createRequest('GET', '/api/config/books-without-cover');
        $response = $this->app->handle($request);
        $content = (string)$response->getBody();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertJsonData([], $response);
    }

    public function testWithPdfExtensionAndWithCover()
    {
        $books = $this->populateBooks();
        foreach ($books as $book) {
            $book->file = new BookFile($book->file->getFilename() .'.pdf');
            $book->book_cover = 'some-data';
            $book->save();
        }

        $request = $this->createRequest('GET', '/api/config/books-without-cover');
        $response = $this->app->handle($request);
        $content = (string)$response->getBody();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertJsonData([], $response);
    }

    public function testWithPdfExtensionAndWithoutCover()
    {
        $books = $this->populateBooks();
        foreach ($books as $book) {
            $book->file = new BookFile($book->file->getFilename() .'.pdf');
            $book->book_cover = null;
            $book->save();
        }
        $arrayBooks = Collection::make($books)->map(function (Book $book) {
            return ['filename' => $book->file->getFilename(), 'book_guid' => $book->book_guid];
        })->toArray();

        $request = $this->createRequest('GET', '/api/config/books-without-cover');
        $response = $this->app->handle($request);
        $content = (string)$response->getBody();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($arrayBooks, json_decode($content, true));
    }

    public function testWithPdfExtensionAndOneWithCover()
    {
        $books = $this->populateBooks();
        foreach ($books as $book) {
            $book->file = new BookFile($book->file->getFilename() .'.pdf');
            $book->book_cover = null;
            $book->save();
        }
        $bookWithCover = $books[1];
        $bookWithCover->book_cover = 'some-data';
        $bookWithCover->save();

        $arrayBooks = Collection::make($books)->filter(function (Book $book) use ($bookWithCover) {
            return $book->book_guid !== $bookWithCover->book_guid;
        })->map(function (Book $book) {
            return ['filename' => $book->file->getFilename(), 'book_guid' => $book->book_guid];
        })->values()->toArray();

        $request = $this->createRequest('GET', '/api/config/books-without-cover');
        $response = $this->app->handle($request);
        $content = (string)$response->getBody();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($arrayBooks, json_decode($content, true));
    }
}

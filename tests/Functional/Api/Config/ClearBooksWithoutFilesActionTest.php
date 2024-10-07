<?php declare(strict_types=1);

namespace Tests\Functional\Api\Config;

use App\Actions\Api\Config\ClearBooksWithoutFilesAction;
use App\Models\Book;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\Functional\AbstractTestCase;
use Tests\PopulateBooksTrait;

#[CoversClass(ClearBooksWithoutFilesAction::class)]
#[CoversClass(Book::class)]
class ClearBooksWithoutFilesActionTest extends AbstractTestCase
{
    use PopulateBooksTrait;


    public function testClearDatabaseFromBooksWithoutFiles()
    {
        $this->setBookLibrarySync(false);
        $books = $this->populateBooks();
        $book = $books[0];
        file_put_contents($this->getLibraryConfig()->getFilepath($book->file->getFilename()), 'test data');

        $this->assertDatabaseCount('books', 3);

        $request = $this->createRequest('POST', '/api/config/clear-books-without-files');
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

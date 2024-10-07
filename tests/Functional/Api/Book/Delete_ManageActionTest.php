<?php declare(strict_types=1);

namespace Tests\Functional\Api\Book;

use App\Actions\Api\Book\ManageAction;
use App\Models\Book;
use App\Models\BookFile;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use Tests\Functional\AbstractTestCase;
use Tests\PopulateBooksTrait;

#[CoversMethod(ManageAction::class, 'deleteBook')]
#[CoversMethod(ManageAction::class, 'asJSON')]
#[CoversClass(Book::class)]
#[CoversClass(BookFile::class)]
class Delete_ManageActionTest extends AbstractTestCase
{
    use PopulateBooksTrait;

    public function testSuccessful()
    {
        $this->setBookLibrarySync(false);
        $books = $this->populateBooks();
        $bookToDelete = $books[0];

        $request = $this->createJsonRequest('POST', '/api/book/manage', [
            'id' => $bookToDelete->book_guid,
            'oper' => 'del'
        ]);
        $response = $this->app->handle($request);
        $this->assertSame('', (string)$response->getBody());

        $this->assertDatabaseCount('books', 2);
        $this->assertDatabaseMissing('books', ['book_guid' => $bookToDelete->book_guid]);
    }

    public function testSuccessfulWithSyncOnWhenFileWasRemoved()
    {
        $this->setBookLibrarySync(false);
        $books = $this->populateBooks();
        $this->setBookLibrarySync(true);
        $bookToDelete = $books[0];
        $this->assertFileDoesNotExist($bookToDelete->file->getFilepath());

        $request = $this->createJsonRequest('POST', '/api/book/manage', [
            'id' => $bookToDelete->book_guid,
            'oper' => 'del'
        ]);
        $response = $this->app->handle($request);
        $content = (string)$response->getBody();
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('', $content);
        $this->assertDatabaseCount('books', 2);
    }

    public function testSuccessfulWithSyncOnWhenFileExists()
    {
        $this->setBookLibrarySync(false);
        $books = $this->populateBooks();
        $this->setBookLibrarySync(true);
        $bookToDelete = $books[0];
        file_put_contents($bookToDelete->file->getFilepath(), 'some data');
        $this->assertFileExists($bookToDelete->file->getFilepath());

        $request = $this->createJsonRequest('POST', '/api/book/manage', [
            'id' => $bookToDelete->book_guid,
            'oper' => 'del'
        ]);
        $response = $this->app->handle($request);
        $content = (string)$response->getBody();
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('', $content);
        $this->assertDatabaseCount('books', 2);
        $this->assertFileDoesNotExist($bookToDelete->file->getFilepath());
    }
}

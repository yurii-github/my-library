<?php declare(strict_types=1);

namespace Tests\Functional\Api\Config;

use App\Actions\Api\Config\DoImportFilesAction;
use App\Models\Book;
use App\Models\BookFile;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\Functional\AbstractTestCase;
use Tests\PopulateBooksTrait;

#[CoversClass(DoImportFilesAction::class)]
#[CoversClass(BookFile::class)]
#[CoversClass(Book::class)]
class DoImportFilesActionTest extends AbstractTestCase
{
    use PopulateBooksTrait;

    public function testDoNothingIfListIsEmpty()
    {
        $this->setBookLibrarySync(false);
        $books = $this->populateBooks();

        $request = $this->createRequest('POST', '/api/config/import-files');
        $response = $this->app->handle($request);
        $content = (string)$response->getBody();

        $this->assertJsonData([
            'data' => [],
            'result' => true,
            'error' => ''
        ], $response);
        $this->assertDatabaseCount('books', 3);
    }


    public function testFilesystemOnlyFilesImport()
    {
        $this->setBookLibrarySync(false);
        $books = $this->populateBooks();
        file_put_contents($this->getLibraryConfig()->getFilepath('fs-only.pdf'), ' some data');
        $this->assertDatabaseCount('books', 3);

        $request = $this->createRequest('POST', '/api/config/import-files');
        $request = $request->withParsedBody([
            'post' => ['fs-only.pdf']
        ]);
        $response = $this->app->handle($request);
        $content = (string)$response->getBody();
        $this->assertJsonData([
            'data' => ['fs-only.pdf'],
            'result' => true,
            'error' => ''
        ], $response);
    }

    public function testFailsWhenFileDoesNotExist()
    {
        $this->setBookLibrarySync(false);
        $books = $this->populateBooks();
        $filename = 'fs-only.pdf';

        $this->assertDatabaseCount('books', 3);

        $request = $this->createRequest('POST', '/api/config/import-files');
        $request = $request->withParsedBody([
            'post' => [$filename]
        ]);
        $response = $this->app->handle($request);
        $content = (string)$response->getBody();
        $this->assertJsonData([
            'data' => [],
            'result' => false,
            'error' => 'Book file does not exist!'
        ], $response);

        $this->assertDatabaseCount('books', 3);
    }
}

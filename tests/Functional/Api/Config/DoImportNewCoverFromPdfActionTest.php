<?php

namespace Tests\Functional\Api\Config;

use App\CoverExtractor;
use App\Models\BookFile;
use Illuminate\Container\Container;
use Tests\Functional\AbstractTestCase;
use Tests\PopulateBooksTrait;

class DoImportNewCoverFromPdfActionTest extends AbstractTestCase
{
    use PopulateBooksTrait;
    
    protected string $actionUrl = '/api/config/import-new-cover-from-pdf';

    protected function withGhostscript()
    {
        $this->getLibraryConfig()->getBook()->ghostscript = '/usr/bin/ghostscript';
    }

    public function testGhostcript_IsNotconfigured()
    {
        $this->getLibraryConfig()->getBook()->ghostscript = null;

        $books = $this->populateBooks();

        $bookWithCover = $books[0];
        $bookWithCover->file = new BookFile('test.pdf');
        $bookWithCover->book_cover = 'some-data';
        $bookWithCover->save();
        copy(self::getTestFilepath('test.pdf'), $bookWithCover->file->getFilepath());

        $request = $this->createJsonRequest('POST', $this->actionUrl, [
            'post' => [
                ['book_guid' => $bookWithCover->book_guid]
            ]
        ]);

        $response = $this->app->handle($request);
        $content = (string)$response->getBody();

        $this->assertDatabaseHas('books', [
            'book_guid' => $bookWithCover->book_guid,
            'book_cover' => $bookWithCover->book_cover,
            'filename' => $bookWithCover->file->getFilename(),
        ]);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertJsonData([
            'data' => [],
            'result' => false,
            'error' => 'Ghostscript is not configured'
        ], $response);
    }

    public function testGhostcript_CoverGetsUpdatedEvenWhenItExists()
    {
        $this->withGhostscript();

        $coverExtractor = new class($this->getLibraryConfig()) extends CoverExtractor {
            protected function buildGhostscriptCommand($srcPdfFile, $outJpegFile)
            {
                if ($srcPdfFile !== 'vfs://base/data/books/test.pdf') {
                    throw new \Exception('Unknown file system in test suite! Please fix your tests!');
                }
                $srcPdfFile = AbstractTestCase::getTestFilepath('test.pdf');
                return parent::buildGhostscriptCommand($srcPdfFile, $outJpegFile);
            }
        };

        /** @var Container $container */
        $container = $this->app->getContainer();
        $container->instance(CoverExtractor::class, $coverExtractor);

        $books = $this->populateBooks();

        $bookWithCover = $books[0];
        $bookWithCover->file = new BookFile('test.pdf');
        $bookWithCover->book_cover = 'some-data';
        $bookWithCover->save();
        copy(self::getTestFilepath('test.pdf'), $bookWithCover->file->getFilepath());

        $request = $this->createJsonRequest('POST', $this->actionUrl, [
            'post' => [
                ['book_guid' => $bookWithCover->book_guid]
            ]
        ]);

        $response = $this->app->handle($request);
        $content = (string)$response->getBody();

        $this->assertDatabaseHas('books', [
            'book_guid' => $bookWithCover->book_guid,
            'book_cover' => file_get_contents(self::getTestFilepath('cover_from_test_pdf.jpg')),
            'filename' => $bookWithCover->file->getFilename(),
        ]);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertJsonData([
            'data' => [
                $bookWithCover->file->getFilename(),
            ],
            'result' => true,
            'error' => null
        ], $response);
    }

    public function testFailsToExtractFromOtherFormatsThanPdf()
    {
        $books = $this->populateBooks();
        $bookWithCover = $books[0];
        $bookWithCover->file = new BookFile($bookWithCover->file->getFilename().'.txt');
        $bookWithCover->book_cover = 'some-data';
        $bookWithCover->save();
        file_put_contents($bookWithCover->file->getFilepath(), 'some data');

        $this->assertDatabaseHas('books', [
            'book_guid' => $bookWithCover->book_guid,
            'book_cover' => $bookWithCover->book_cover,
        ]);

        $request = $this->createJsonRequest('POST', $this->actionUrl, [
            'post' => [
                ['book_guid' => $bookWithCover->book_guid]
            ]
        ]);

        $response = $this->app->handle($request);
        $content = (string)$response->getBody();

        $this->assertDatabaseHas('books', [
            'book_guid' => $bookWithCover->book_guid,
            'book_cover' => $bookWithCover->book_cover,
            'filename' => $bookWithCover->file->getFilename(),
        ]);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertJsonData([
            'data' => [],
            'result' => false,
            'error' => "Unsupported book format for '{$bookWithCover->file->getFilepath()}'"
        ], $response);
    }

    public function testFailsToExtractFromInvalidPdfFile()
    {
        $this->withGhostscript();
        
        $books = $this->populateBooks();
        $bookWithCover = $books[0];
        $bookWithCover->file = new BookFile($bookWithCover->file->getFilename().'.pdf');
        $bookWithCover->book_cover = 'some-data';
        $bookWithCover->save();
        file_put_contents($bookWithCover->file->getFilepath(), 'some invalid data');

        $this->assertDatabaseHas('books', [
            'book_guid' => $bookWithCover->book_guid,
            'book_cover' => $bookWithCover->book_cover,
        ]);

        $request = $this->createJsonRequest('POST', $this->actionUrl, [
            'post' => [
                ['book_guid' => $bookWithCover->book_guid]
            ]
        ]);

        $response = $this->app->handle($request);
        $content = (string)$response->getBody();

        $this->assertDatabaseHas('books', [
            'book_guid' => $bookWithCover->book_guid,
            'book_cover' => $bookWithCover->book_cover,
            'filename' => $bookWithCover->file->getFilename(),
        ]);

        $this->assertSame(200, $response->getStatusCode());
        $content = (string)$response->getBody();
        $content = json_decode($content, true);
        $this->assertSame([], $content['data']);
        $this->assertSame(false, $content['result']);
        $this->assertStringStartsWith('Failed to convert from <b>vfs://base/data/books/filename-1.pdf</b>', $content['error']);
    }

    public function testDoesNothingIfBookFileDoesNotExist()
    {
        $books = $this->populateBooks();
        $bookWithCover = $books[0];
        $bookWithCover->file = new BookFile($bookWithCover->file->getFilename().'.pdf');
        $bookWithCover->book_cover = 'some-data';
        $bookWithCover->save();

        $this->assertDatabaseHas('books', [
            'book_guid' => $bookWithCover->book_guid,
            'book_cover' => $bookWithCover->book_cover,
        ]);

        $request = $this->createJsonRequest('POST', $this->actionUrl, [
            'post' => [
                ['book_guid' => $bookWithCover->book_guid]
            ]
        ]);

        $response = $this->app->handle($request);
        $content = (string)$response->getBody();

        $this->assertDatabaseHas('books', [
            'book_guid' => $bookWithCover->book_guid,
            'book_cover' => $bookWithCover->book_cover,
            'filename' => $bookWithCover->file->getFilename(),
        ]);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertJsonData([
            'data' => [],
            'result' => false,
            'error' => "Book file '{$bookWithCover->file->getFilepath()}' does not exist!"
        ], $response);
    }

    public function testDoesNothingIfNoBooksAreProvided()
    {
        $this->populateBooks();

        $request = $this->createJsonRequest('POST', $this->actionUrl);

        $response = $this->app->handle($request);
        $content = (string)$response->getBody();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertJsonData([
            'data' => [],
            'result' => true,
            'error' => null
        ], $response);
    }
}
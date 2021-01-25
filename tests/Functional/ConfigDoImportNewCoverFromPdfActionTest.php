<?php

namespace Tests\Functional;

use App\CoverExtractor;
use Illuminate\Container\Container;
use Tests\PopulateBooksTrait;

class ConfigDoImportNewCoverFromPdfActionTest extends AbstractTestCase
{
    use PopulateBooksTrait;

    protected function withGhostscript()
    {
        $this->getLibraryConfig()->getBook()->ghostscript = '/usr/bin/ghostscript';
    }

    public function testGhostcript_IsNotconfigured()
    {
        $this->getLibraryConfig()->getBook()->ghostscript = null;

        $books = $this->populateBooks();

        $bookWithCover = $books[0];
        $bookWithCover->filename = 'test.pdf';
        $bookWithCover->book_cover = 'some-data';
        $bookWithCover->save();
        copy(dirname(__DIR__) . '/data/test.pdf', $bookWithCover->getFilepath());

        $request = $this->createJsonRequest('POST', '/config/import-new-cover-from-pdf', [
            'post' => [
                ['book_guid' => $bookWithCover->book_guid]
            ]
        ]);

        $response = $this->app->handle($request);
        $content = (string)$response->getBody();

        $this->assertDatabaseHas('books', [
            'book_guid' => $bookWithCover->book_guid,
            'book_cover' => $bookWithCover->book_cover,
            'filename' => $bookWithCover->filename,
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
                $srcPdfFile = dirname(__DIR__) . '/data/test.pdf';
                return parent::buildGhostscriptCommand($srcPdfFile, $outJpegFile);
            }
        };

        /** @var Container $container */
        $container = $this->app->getContainer();
        $container->instance(CoverExtractor::class, $coverExtractor);

        $books = $this->populateBooks();

        $bookWithCover = $books[0];
        $bookWithCover->filename = 'test.pdf';
        $bookWithCover->book_cover = 'some-data';
        $bookWithCover->save();
        copy(dirname(__DIR__) . '/data/test.pdf', $bookWithCover->getFilepath());

        $request = $this->createJsonRequest('POST', '/config/import-new-cover-from-pdf', [
            'post' => [
                ['book_guid' => $bookWithCover->book_guid]
            ]
        ]);

        $response = $this->app->handle($request);
        $content = (string)$response->getBody();

        $this->assertDatabaseHas('books', [
            'book_guid' => $bookWithCover->book_guid,
            'book_cover' => file_get_contents(dirname(__DIR__) . '/data/cover_from_test_pdf.jpg'),
            'filename' => $bookWithCover->filename,
        ]);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertJsonData([
            'data' => [
                $bookWithCover->filename,
            ],
            'result' => true,
            'error' => null
        ], $response);
    }

    public function testFailsToExtractFromOtherFormatsThanPdf()
    {
        $books = $this->populateBooks();
        $bookWithCover = $books[0];
        $bookWithCover->filename .= '.txt';
        $bookWithCover->book_cover = 'some-data';
        $bookWithCover->save();
        file_put_contents($bookWithCover->getFilepath(), 'some data');

        $this->assertDatabaseHas('books', [
            'book_guid' => $bookWithCover->book_guid,
            'book_cover' => $bookWithCover->book_cover,
        ]);

        $request = $this->createJsonRequest('POST', '/config/import-new-cover-from-pdf', [
            'post' => [
                ['book_guid' => $bookWithCover->book_guid]
            ]
        ]);

        $response = $this->app->handle($request);
        $content = (string)$response->getBody();

        $this->assertDatabaseHas('books', [
            'book_guid' => $bookWithCover->book_guid,
            'book_cover' => $bookWithCover->book_cover,
            'filename' => $bookWithCover->filename,
        ]);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertJsonData([
            'data' => [],
            'result' => false,
            'error' => "Unsupported book format for '{$bookWithCover->getFilepath()}'"
        ], $response);
    }

    public function testFailsToExtractFromInvalidPdfFile()
    {
        $this->withGhostscript();
        
        $books = $this->populateBooks();
        $bookWithCover = $books[0];
        $bookWithCover->filename .= '.pdf';
        $bookWithCover->book_cover = 'some-data';
        $bookWithCover->save();
        file_put_contents($bookWithCover->getFilepath(), 'some invalid data');

        $this->assertDatabaseHas('books', [
            'book_guid' => $bookWithCover->book_guid,
            'book_cover' => $bookWithCover->book_cover,
        ]);

        $request = $this->createJsonRequest('POST', '/config/import-new-cover-from-pdf', [
            'post' => [
                ['book_guid' => $bookWithCover->book_guid]
            ]
        ]);

        $response = $this->app->handle($request);
        $content = (string)$response->getBody();

        $this->assertDatabaseHas('books', [
            'book_guid' => $bookWithCover->book_guid,
            'book_cover' => $bookWithCover->book_cover,
            'filename' => $bookWithCover->filename,
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
        $bookWithCover->filename .= '.pdf';
        $bookWithCover->book_cover = 'some-data';
        $bookWithCover->save();

        $this->assertDatabaseHas('books', [
            'book_guid' => $bookWithCover->book_guid,
            'book_cover' => $bookWithCover->book_cover,
        ]);

        $request = $this->createJsonRequest('POST', '/config/import-new-cover-from-pdf', [
            'post' => [
                ['book_guid' => $bookWithCover->book_guid]
            ]
        ]);

        $response = $this->app->handle($request);
        $content = (string)$response->getBody();

        $this->assertDatabaseHas('books', [
            'book_guid' => $bookWithCover->book_guid,
            'book_cover' => $bookWithCover->book_cover,
            'filename' => $bookWithCover->filename,
        ]);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertJsonData([
            'data' => [],
            'result' => false,
            'error' => "Book file '{$bookWithCover->getFilepath()}' does not exist!"
        ], $response);
    }

    public function testDoesNothingIfNoBooksAreProvided()
    {
        $this->populateBooks();

        $request = $this->createJsonRequest('POST', '/config/import-new-cover-from-pdf');

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
<?php

namespace Tests\Functional\Api\Config;

use Tests\Functional\AbstractTestCase;
use Tests\PopulateBooksTrait;

class CheckFilesActionTest extends AbstractTestCase
{
    use PopulateBooksTrait;


    public function testComplexCheck()
    {
        $this->setBookLibrarySync(false);
        $books = $this->populateBooks();

        $bookWithFile = $books[0];
        $config = $this->getLibraryConfig();
        file_put_contents($config->getFilepath($bookWithFile->file->getFilename()), 'some data');

        $fsOnlyFilename = 'fs-only.pdf';
        file_put_contents($config->getFilepath($fsOnlyFilename), 'some data 2');

        $request = $this->createRequest('GET', '/api/config/check-files');
        $request = $request->withQueryParams([
            'book_guid' => $books[0]->book_guid
        ]);

        $response = $this->app->handle($request);
        $c = (string)$response->getBody();
        $this->assertSame(200, $response->getStatusCode());
        $resp = json_decode((string)$response->getBody());

        $this->assertCount(2, $resp->db, 'db only records does not match');
        $this->assertEqualsCanonicalizing(["filename-2", "filename-3"], $resp->db, 'filename of db only files does not match');
        $this->assertCount(1, $resp->fs, 'file system only file count does not match');
        $this->assertSame($fsOnlyFilename, $resp->fs[0], 'filename of file system only file does not match');
    }
}
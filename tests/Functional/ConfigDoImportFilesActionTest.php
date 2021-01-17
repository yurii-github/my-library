<?php

namespace Tests\Functional;

use Tests\PopulateBooksTrait;

class ConfigDoImportFilesActionTest extends AbstractTestCase
{
    use PopulateBooksTrait;


    public function testDoNothingIfListIsEmpty()
    {
        $this->setBookLibrarySync(false);
        $books = $this->populateBooks();

        $request = $this->createRequest('POST', '/config/import-files');
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

        $request = $this->createRequest('POST', '/config/import-files');
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
}
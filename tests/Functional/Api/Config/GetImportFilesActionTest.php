<?php

namespace Tests\Functional\Api\Config;

use Tests\Functional\AbstractTestCase;
use Tests\PopulateBooksTrait;

class GetImportFilesActionTest extends AbstractTestCase
{
    use PopulateBooksTrait;


    public function testListFilesystemOnlyFiles()
    {
        $this->setBookLibrarySync(false);
        $this->populateBooks();
        file_put_contents($this->getLibraryConfig()->getFilepath('fs-only.pdf'), ' some data');

        $request = $this->createJsonRequest('GET', '/config/import-files');
        $response = $this->app->handle($request);
        $this->assertJsonData(['fs-only.pdf'], $response);
    }


}
<?php declare(strict_types=1);

namespace Tests\Functional\Api\Config;

use App\Actions\Api\Config\GetImportFilesAction;
use App\Configuration\Configuration;
use App\Models\Book;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\Functional\AbstractTestCase;
use Tests\PopulateBooksTrait;

#[CoversClass(GetImportFilesAction::class)]
#[CoversClass(Configuration::class)]
#[CoversClass(Book::class)]
class GetImportFilesActionTest extends AbstractTestCase
{
    use PopulateBooksTrait;

    public function testListFilesystemOnlyFiles()
    {
        $this->setBookLibrarySync(false);
        $this->populateBooks();
        file_put_contents($this->getLibraryConfig()->getFilepath('fs-only.pdf'), ' some data');

        $request = $this->createJsonRequest('GET', '/api/config/import-files');
        $response = $this->app->handle($request);
        $this->assertJsonData(['fs-only.pdf'], $response);
    }


}

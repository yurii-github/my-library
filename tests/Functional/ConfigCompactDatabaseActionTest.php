<?php

namespace Tests\Functional;

use App\Models\Book;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Support\Collection;
use Tests\PopulateBooksTrait;

class ConfigCompactDatabaseActionTest extends AbstractTestCase
{
    use PopulateBooksTrait;

    public function testAction()
    {
        $config = $this->getLibraryConfig();

        try {
            $books = $this->populateBooks();

            if ($config->getDatabase()->format === 'sqlite' && $config->getDatabase()->filename === ':memory') {
                // do nothing
            } else {
                $db = $this->app->getContainer()->get('db');
                assert($db instanceof Manager);
                $db->getConnection()->reconnect();
                $this->assertFalse($db->getConnection()->getPdo()->inTransaction());
            }

            $request = $this->createRequest('POST', '/config/compact-database');
            $response = $this->app->handle($request);

            $content = (string)$response->getBody();
            $this->assertSame(200, $response->getStatusCode());

            if ($config->getDatabase()->format === 'mysql') {
                $this->assertStringContainsString('MYSQL COMPACT', $content);
            } else {
                $this->assertStringContainsString('SQLITE COMPACT', $content);
            }
        } finally {
            if ($config->getDatabase()->format === 'sqlite' && $config->getDatabase()->filename === ':memory:') {
                // do nothing
            } else {
                Book::query()->whereIn('book_guid', Collection::make($books)->pluck('book_guid'))->delete();
            }
        }
    }
}
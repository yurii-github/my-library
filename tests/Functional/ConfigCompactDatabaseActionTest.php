<?php

namespace Tests\Functional;

use App\Models\Book;
use Illuminate\Support\Collection;
use Tests\PopulateBooksTrait;

class ConfigCompactDatabaseActionTest extends AbstractTestCase
{
    use PopulateBooksTrait;

    public function testAction()
    {
        try {
            $books = $this->populateBooks();
            $request = $this->createRequest('POST', '/config/compact-database');
            $response = $this->app->handle($request);

            $this->assertSame(200, $response->getStatusCode());
            $content = (string)$response->getBody();
            $this->assertStringContainsString('MYSQL COMPACT', $content);
        } finally {
            Book::query()->whereIn('book_guid', Collection::make($books)->pluck('book_guid'))->delete();
        }
    }
}
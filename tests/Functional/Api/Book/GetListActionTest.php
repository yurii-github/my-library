<?php

namespace Tests\Functional\Api\Book;

use App\Models\Category;
use Illuminate\Support\Collection;
use Tests\Functional\AbstractTestCase;
use Tests\PopulateBooksTrait;
use Tests\PopulateCategoriesTrait;

class GetListActionTest extends AbstractTestCase
{
    use PopulateBooksTrait;
    use PopulateCategoriesTrait;

    function testCombinedFilter()
    {
        $books = $this->populateBooks();
        $request = $this->createJsonRequest('GET', '/api/book');
        $request = $request->withQueryParams([
            'filters' => json_encode([
                'groupOp' => 'AND',
                'rules' => [
                    [
                        'op' => 'bw',
                        'field' => 'title',
                        'data' => 'title'
                    ],
                    [
                        'op' => 'eq',
                        'field' => 'filename',
                        'data' => 'filename-2'
                    ]
                ]
            ]),
        ]);
        $response = $this->app->handle($request);
        $this->assertSame(200, $response->getStatusCode());
        $content = (string)$response->getBody();
        $data = json_decode($content, true);
        $this->assertSame(1, $data['page']);
        $this->assertSame(1, $data['total']);
        $this->assertSame(1, $data['records']);
        $this->assertIsArray($data['rows']);
        $this->assertCount(1, $data['rows']);
        $this->assertSame($books[1]->book_guid, $data['rows'][0]['id']);
    }


    function testUnknownConditionInFilter()
    {
        $books = $this->populateBooks();
        $request = $this->createJsonRequest('GET', '/api/book');
        $request = $request->withQueryParams([
            'filters' => json_encode([
                'rules' => [[
                    'op' => 'unknown-condition',
                    'field' => 'title',
                    'data' => '#2'
                ]]
            ]),
        ]);
        $response = $this->app->handle($request);
        $response->getBody()->rewind();
        $content = $response->getBody()->getContents();

        $this->assertSame(200, $response->getStatusCode());
        $data = json_decode($content, true);
        $this->assertSame(1, $data['page']);
        $this->assertSame(1, $data['total']);
        $this->assertSame(3, $data['records']);
        $this->assertIsArray($data['rows']);
        $this->assertCount(3, $data['rows']);
        $this->assertEqualsCanonicalizing(
            Collection::make([$books[0], $books[1], $books[2]])->pluck('book_guid')->all(),
            Collection::make($data['rows'])->pluck('id')->all()
        );
    }


    function testSimpleFilter()
    {
        $books = $this->populateBooks();
        $request = $this->createJsonRequest('GET', '/api/book');
        $request = $request->withQueryParams([
            'filters' => json_encode([
                'rules' => [[
                    'op' => 'bw',
                    'field' => 'title',
                    'data' => '#2'
                ]]
            ]),
            'rows' => 1,
        ]);
        $response = $this->app->handle($request);
        $response->getBody()->rewind();
        $this->assertSame(200, $response->getStatusCode());
        $data = json_decode((string)$response->getBody(), true);
        $this->assertSame(1, $data['page']);
        $this->assertSame(1, $data['total']);
        $this->assertSame(1, $data['records']);
        $this->assertIsArray($data['rows']);
        $this->assertCount(1, $data['rows']);
        $this->assertSame($books[1]->book_guid, $data['rows'][0]['id']);
        $this->assertEquals($books[1]->title, $data['rows'][0]['cell']['title']);
    }

    public function testFilterByCategories()
    {
        $books = $this->populateBooks();
        $categories = $this->populateCategories();
        $category = new Category();
        $category->title = 'new category 3';
        $category->save();
        $categories[] = $category;
        $bookWithCategory = $books[0];
        $bookWithCategory->categories()->attach($categories[1]);
        $bookWithCategory->refresh();

        $request = $this->createJsonRequest('GET', '/api/book');
        $request = $request->withQueryParams([
            'filterCategories' => "{$categories[0]->guid},{$categories[1]->guid}"
        ]);
        $response = $this->app->handle($request);

        $this->assertSame(200, $response->getStatusCode());
        $data = json_decode((string)$response->getBody(), true);
        $this->assertSame(1, $data['page']);
        $this->assertSame(1, $data['total']);
        $this->assertSame(1, $data['records']);
        $this->assertIsArray($data['rows']);
        $this->assertCount(1, $data['rows']);
        $this->assertSame($bookWithCategory->book_guid, $data['rows'][0]['id']);
        $this->assertEqualsCanonicalizing([
            'created_date' => $bookWithCategory->created_date->toIso8601ZuluString('microsecond'),
            'book_guid' => $bookWithCategory->book_guid,
            'favorite' => $bookWithCategory->favorite,
            'read' => $bookWithCategory->read,
            'year' => $bookWithCategory->year,
            'title' => $bookWithCategory->title,
            'isbn13' => $bookWithCategory->isbn13,
            'author' => $bookWithCategory->author,
            'publisher' => $bookWithCategory->publisher,
            'ext' => $bookWithCategory->ext,
            'filename' => $bookWithCategory->file->getFilename(),
        ], $data['rows'][0]['cell']);
    }

    function testPagination()
    {
        $books = $this->populateBooks();
        $request = $this->createJsonRequest('GET', '/api/book');
        $request = $request->withQueryParams([
            'page' => 2,
            'rows' => 1,
        ]);
        $response = $this->app->handle($request);
        $response->getBody()->rewind();
        $content = $response->getBody()->getContents();

        $this->assertSame(200, $response->getStatusCode());
        $data = json_decode($content, true);

        $this->assertSame(2, $data['page']);
        $this->assertSame(3, $data['total']);
        $this->assertSame(3, $data['records']);
        $this->assertIsArray($data['rows']);
        $this->assertCount(1, $data['rows']);
        $this->assertTrue(in_array($data['rows'][0]['id'], Collection::make($books)->pluck('book_guid')->all()));
    }


    function testNoFilters()
    {
        $books = $this->populateBooks();
        $request = $this->createJsonRequest('GET', '/api/book');
        $response = $this->app->handle($request);
        $this->assertSame(200, $response->getStatusCode());
        $data = json_decode((string)$response->getBody(), true);

        $this->assertSame(1, $data['page']);
        $this->assertSame(1, $data['total']);
        $this->assertSame(3, $data['records']);
        $this->assertIsArray($data['rows']);
        $this->assertCount(3, $data['rows']);

        $this->assertEqualsCanonicalizing(
            Collection::make([$books[0], $books[1], $books[2]])->pluck('book_guid')->all(),
            Collection::wrap($data['rows'])->pluck('id')->all()
        );
    }

}
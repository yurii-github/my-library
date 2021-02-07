<?php

namespace Tests\Functional;

use Tests\PopulateBooksTrait;
use Tests\PopulateCategoriesTrait;

class GetBookCategoryListActionTest extends AbstractTestCase
{
    use PopulateBooksTrait;
    use PopulateCategoriesTrait;

    function testNoFilters()
    {
        $categories = $this->populateCategories();

        $request = $this->createJsonRequest('GET', '/api/category');
        $response = $this->app->handle($request);
        $this->assertSame(200, $response->getStatusCode());
        $data = json_decode((string)$response->getBody(), true);

        $this->assertSame(1, $data['page']);
        $this->assertSame(1, $data['total']);
        $this->assertSame(2, $data['records']);
        $this->assertIsArray($data['rows']);
        $this->assertCount(2, $data['rows']);
        $this->assertEqualsCanonicalizing(
            [
                [
                    'id' => $categories[0]->guid,
                    'cell' => [
                        'guid' => $categories[0]->guid,
                        'title' => $categories[0]->title,
                        'marker' => '0'
                    ]
                ],
                [
                    'id' => $categories[1]->guid,
                    'cell' => [
                        'guid' => $categories[1]->guid,
                        'title' => $categories[1]->title,
                        'marker' => '0'
                    ]
                ]
            ],
            $data['rows']
        );
    }


    function testBookMarkerIsSet()
    {
        $categories = $this->populateCategories();
        $books = $this->populateBooks();
        $bookWithMarker = $books[0];
        $bookWithMarker->categories()->attach($categories[1]);
        
        $this->assertDatabaseCount('books_categories', 1);
        
        $request = $this->createJsonRequest('GET', '/api/category');
        $request = $request->withQueryParams(['nodeid' => $bookWithMarker->book_guid]);
        $response = $this->app->handle($request);
        $this->assertSame(200, $response->getStatusCode());
        $data = json_decode((string)$response->getBody(), true);

        $this->assertSame(1, $data['page']);
        $this->assertSame(1, $data['total']);
        $this->assertSame(2, $data['records']);
        $this->assertIsArray($data['rows']);
        $this->assertCount(2, $data['rows']);
        $this->assertEqualsCanonicalizing(
            [
                [
                    'id' => $categories[0]->guid,
                    'cell' => [
                        'guid' => $categories[0]->guid,
                        'title' => $categories[0]->title,
                        'marker' => '0'
                    ]
                ],
                [
                    'id' => $categories[1]->guid,
                    'cell' => [
                        'guid' => $categories[1]->guid,
                        'title' => $categories[1]->title,
                        'marker' => '1' // marker is set!
                    ]
                ]
            ],
            $data['rows']
        );
    }
}
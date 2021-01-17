<?php

namespace Tests\Functional;

use Tests\PopulateBooksTrait;

class GetBookListActionTest extends AbstractTestCase
{
    use PopulateBooksTrait;


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
        $this->assertSame(3, $data['total']);
        $this->assertSame(3, $data['records']);
        $this->assertIsArray($data['rows']);
        $this->assertCount(3, $data['rows']);
        $this->assertSame($books[0]->book_guid, $data['rows'][0]['id']);
        $this->assertSame($books[1]->book_guid, $data['rows'][1]['id']);
        $this->assertSame($books[2]->book_guid, $data['rows'][2]['id']);
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
        $this->assertSame(1, $data['total']);
        $this->assertSame(3, $data['records']);
        $this->assertIsArray($data['rows']);
        $this->assertCount(1, $data['rows']);
        $this->assertSame($books[1]->book_guid, $data['rows'][0]['id']);
    }


    function testNoFilters()
    {
        $books = $this->populateBooks();
        $response = $this->getBooksResponse();
        $content = $response->getBody()->getContents();

        $this->assertSame(200, $response->getStatusCode());
        $data = json_decode($content, true);

        $this->assertSame(1, $data['page']);
        $this->assertSame(3, $data['total']);
        $this->assertSame(3, $data['records']);
        $this->assertIsArray($data['rows']);
        $this->assertCount(3, $data['rows']);

        $this->assertSame($books[0]->book_guid, $data['rows'][0]['id']);
        $this->assertSame($books[1]->book_guid, $data['rows'][1]['id']);
    }

    protected function getBooksResponse(array $data = null)
    {
        $request = $this->createJsonRequest('GET', '/api/book', $data);
        $response = $this->app->handle($request);
        $response->getBody()->rewind();

        return $response;
    }
}
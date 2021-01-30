<?php

namespace Tests\Functional;

use App\Models\Category;
use Tests\PopulateBooksTrait;
use Tests\PopulateCategoriesTrait;

class ManageBookCategoryActionTest extends AbstractTestCase
{
    use PopulateBooksTrait;
    use PopulateCategoriesTrait;

    public function testUnsupportedOperationThrowsException()
    {
        $request = $this->createJsonRequest('POST', '/api/category/manage');
        $response = $this->app->handle($request);

        $this->assertSame(400, $response->getStatusCode());
        $this->assertJsonData(['error' => 'Unsupported operation!'], $response);
    }

    public function testAddCategory()
    {
        list($category, $category2) = $this->populateCategories();
        $this->assertDatabaseCount('categories', 2);

        $request = $this->createJsonRequest('POST', '/api/category/manage', [
            'oper' => 'add',
            'title' => 'new category 3'
        ]);

        $response = $this->app->handle($request);

        $content = (string)$response->getBody();
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('', $content);
        $this->assertDatabaseCount('categories', 3);
        $this->assertDatabaseHas('categories', ['title' => $category->title]);
        $this->assertDatabaseHas('categories', ['title' => 'new category 2']);
        $this->assertDatabaseHas('categories', ['title' => 'new category 3']);

        $categories = Category::all();

        foreach ($categories as $category) {
            $this->assertTrue(
                (bool)preg_match('/[A-Z0-9]{8}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{12}/', $category->guid),
                "'{ $category->guid}' is in wrong format"
            );

        }
    }

    public function testAddCategory_TitleIsRequired()
    {
        list($category, $category2) = $this->populateCategories();
        $this->assertDatabaseCount('categories', 2);

        $request = $this->createJsonRequest('POST', '/api/category/manage', [
            'oper' => 'add',
        ]);

        $response = $this->app->handle($request);
        $content = (string)$response->getBody();
        $this->assertSame(422, $response->getStatusCode());
        $this->assertJsonData(['title' => ['validation.required']], $response); // TODO: fix validation text
        $this->assertDatabaseCount('categories', 2);
        $this->assertDatabaseHas('categories', ['guid' => $category->guid, 'title' => $category->title]);
        $this->assertDatabaseHas('categories', ['guid' => $category2->guid, 'title' => $category2->title]);
    }

    public function testDeleteCategory_IdIsRequired()
    {
        list($category, $category2) = $this->populateCategories();
        $this->assertDatabaseCount('categories', 2);

        $request = $this->createJsonRequest('POST', '/api/category/manage', [
            'oper' => 'del',
            'title' => 'new category 2'
        ]);

        $response = $this->app->handle($request);

        $content = (string)$response->getBody();
        $this->assertSame(422, $response->getStatusCode());
        $this->assertJsonData(['id' => ['validation.required']], $response);
        $this->assertDatabaseCount('categories', 2);
        $this->assertDatabaseHas('categories', ['guid' => $category->guid, 'title' => $category->title]);
        $this->assertDatabaseHas('categories', ['guid' => $category2->guid, 'title' => $category2->title]);
    }

    public function testDeleteCategory_CategoryDoesNotExist()
    {
        list($category, $category2) = $this->populateCategories();
        $this->assertDatabaseCount('categories', 2);

        $request = $this->createJsonRequest('POST', '/api/category/manage', [
            'oper' => 'del',
            'id' => 'unknown-id'
        ]);

        $response = $this->app->handle($request);

        $content = (string)$response->getBody();
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('', $content);
        $this->assertDatabaseCount('categories', 2);
        $this->assertDatabaseHas('categories', ['guid' => $category->guid, 'title' => $category->title]);
        $this->assertDatabaseHas('categories', ['guid' => $category2->guid, 'title' => $category2->title]);
    }

    public function testDeleteCategory()
    {
        list($category, $category2) = $this->populateCategories();
        $this->assertDatabaseCount('categories', 2);
        $this->assertDatabaseHas('categories', ['guid' => $category->guid, 'title' => $category->title]);
        $this->assertDatabaseHas('categories', ['guid' => $category2->guid, 'title' => $category2->title]);

        $request = $this->createJsonRequest('POST', '/api/category/manage', [
            'oper' => 'del',
            'id' => $category->guid
        ]);

        $response = $this->app->handle($request);

        $content = (string)$response->getBody();
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('', $content);
        $this->assertDatabaseCount('categories', 1);
        $this->assertDatabaseHas('categories', ['title' => $category2->title]);
    }

    public function testEditCategory_NothingToDo()
    {
        list($category, $category2) = $this->populateCategories();
        $this->assertDatabaseCount('categories', 2);
        $this->assertDatabaseHas('categories', ['guid' => $category->guid, 'title' => $category->title]);
        $this->assertDatabaseHas('categories', ['guid' => $category2->guid, 'title' => $category2->title]);

        $request = $this->createJsonRequest('POST', '/api/category/manage', [
            'oper' => 'edit',
            'id' => $category->guid
        ]);

        $response = $this->app->handle($request);

        $content = (string)$response->getBody();
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('', $content);
        $this->assertDatabaseCount('categories', 2);
        $this->assertDatabaseHas('categories', ['guid' => $category->guid, 'title' => $category->title]);
        $this->assertDatabaseHas('categories', ['guid' => $category2->guid, 'title' => $category2->title]);
    }

    public function testEditCategory_ChangeTitle()
    {
        list($category, $category2) = $this->populateCategories();
        $this->assertDatabaseCount('categories', 2);
        $this->assertDatabaseHas('categories', ['guid' => $category->guid, 'title' => $category->title]);
        $this->assertDatabaseHas('categories', ['guid' => $category2->guid, 'title' => $category2->title]);

        $request = $this->createJsonRequest('POST', '/api/category/manage', [
            'oper' => 'edit',
            'id' => $category->guid,
            'title' => 'updated title'
        ]);

        $response = $this->app->handle($request);

        $content = (string)$response->getBody();
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('', $content);
        $this->assertDatabaseCount('categories', 2);
        $this->assertDatabaseHas('categories', ['guid' => $category->guid, 'title' => 'updated title']);
        $this->assertDatabaseHas('categories', ['guid' => $category2->guid, 'title' => $category2->title]);
    }

    public function testEditCategory_BookMarkerMustBeBoolean()
    {
        list($category, $category2) = $this->populateCategories();
        $books = $this->populateBooks();
        $bookWithMarker = $books[0];

        $this->assertDatabaseCount('categories', 2);
        $this->assertDatabaseHas('categories', ['guid' => $category->guid, 'title' => $category->title]);
        $this->assertDatabaseHas('categories', ['guid' => $category2->guid, 'title' => $category2->title]);
        $this->assertDatabaseCount('books_categories', 0);
        $this->assertCount(0, $bookWithMarker->categories);

        $request = $this->createJsonRequest('POST', '/api/category/manage', [
            'oper' => 'edit',
            'id' => $category->guid,
            'marker' => 'true'
        ]);
        $request = $request->withQueryParams(['nodeid' => $bookWithMarker->book_guid]);

        $response = $this->app->handle($request);

        $content = (string)$response->getBody();
        $this->assertSame(422, $response->getStatusCode());
        $this->assertJsonData(['marker' => ['validation.boolean']], $response);
        $this->assertDatabaseCount('categories', 2);
        $this->assertDatabaseHas('categories', ['guid' => $category->guid, 'title' => $category->title]);
        $this->assertDatabaseHas('categories', ['guid' => $category2->guid, 'title' => $category2->title]);
        $this->assertDatabaseCount('books_categories', 0);
    }

    public function testEditCategory_CanSetBookMarkerAndUpdateCategoryTitle()
    {
        list($category, $category2) = $this->populateCategories();
        $books = $this->populateBooks();
        $bookWithMarker = $books[0];

        $this->assertDatabaseCount('categories', 2);
        $this->assertDatabaseHas('categories', ['guid' => $category->guid, 'title' => $category->title]);
        $this->assertDatabaseHas('categories', ['guid' => $category2->guid, 'title' => $category2->title]);
        $this->assertDatabaseCount('books_categories', 0);
        $this->assertCount(0, $bookWithMarker->categories);

        $request = $this->createJsonRequest('POST', '/api/category/manage', [
            'oper' => 'edit',
            'id' => $category->guid,
            'title' => 'updated title',
            'marker' => true
        ]);
        $request = $request->withQueryParams(['nodeid' => $bookWithMarker->book_guid]);

        $response = $this->app->handle($request);

        $content = (string)$response->getBody();
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('', $content);
        $this->assertDatabaseCount('categories', 2);
        $this->assertDatabaseHas('categories', ['guid' => $category->guid, 'title' => 'updated title']);
        $this->assertDatabaseHas('categories', ['guid' => $category2->guid, 'title' => $category2->title]);
        $this->assertDatabaseCount('books_categories', 1);
        $bookWithMarker->refresh();
        $this->assertCount(1, $bookWithMarker->categories);
        $this->assertInstanceOf(Category::class, $bookWithMarker->categories->first());
        $this->assertTrue($category->is($bookWithMarker->categories->first()));
    }

    public function testEditCategory_CanUnsetBookMarkerAndUpdateCategoryTitle()
    {
        list($category, $category2) = $this->populateCategories();
        $books = $this->populateBooks();
        $bookWithMarker = $books[0];
        $bookWithMarker->categories()->attach($category);
        $bookWithMarker->refresh();

        $this->assertDatabaseCount('categories', 2);
        $this->assertDatabaseHas('categories', ['guid' => $category->guid, 'title' => $category->title]);
        $this->assertDatabaseHas('categories', ['guid' => $category2->guid, 'title' => $category2->title]);
        $this->assertDatabaseCount('books_categories', 1);
        $this->assertCount(1, $bookWithMarker->categories);

        $request = $this->createJsonRequest('POST', '/api/category/manage', [
            'oper' => 'edit',
            'id' => $category->guid,
            'title' => 'updated title',
            'marker' => false
        ]);
        $request = $request->withQueryParams(['nodeid' => $bookWithMarker->book_guid]);

        $response = $this->app->handle($request);

        $content = (string)$response->getBody();
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('', $content);
        $this->assertDatabaseCount('categories', 2);
        $this->assertDatabaseHas('categories', ['guid' => $category->guid, 'title' => 'updated title']);
        $this->assertDatabaseHas('categories', ['guid' => $category2->guid, 'title' => $category2->title]);
        $this->assertDatabaseCount('books_categories', 0);
        $bookWithMarker->refresh();
        $this->assertCount(0, $bookWithMarker->categories);
    }

    public function testEditCategory_BookMarkerIsRequiredWithNodeId()
    {
        list($category, $category2) = $this->populateCategories();
        $books = $this->populateBooks();
        $bookWithMarker = $books[0];

        $this->assertDatabaseCount('categories', 2);
        $this->assertDatabaseHas('categories', ['guid' => $category->guid, 'title' => $category->title]);
        $this->assertDatabaseHas('categories', ['guid' => $category2->guid, 'title' => $category2->title]);
        $this->assertDatabaseCount('books_categories', 0);
        $this->assertCount(0, $bookWithMarker->categories);

        $request = $this->createJsonRequest('POST', '/api/category/manage', [
            'oper' => 'edit',
            'id' => $category->guid,
            'title' => 'updated title',
        ]);
        $request = $request->withQueryParams(['nodeid' => $bookWithMarker->book_guid]);

        $response = $this->app->handle($request);

        $content = (string)$response->getBody();
        $this->assertSame(422, $response->getStatusCode());
        $this->assertJsonData(['marker' => ['validation.required_with']], $response);
        $this->assertDatabaseCount('categories', 2);
        $this->assertDatabaseHas('categories', ['guid' => $category->guid, 'title' => $category->title]);
        $this->assertDatabaseHas('categories', ['guid' => $category2->guid, 'title' => $category2->title]);
    }


}
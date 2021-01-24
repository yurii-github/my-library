<?php

namespace Tests\Functional;

use App\Models\Category;
use Tests\PopulateBooksTrait;

class ManageBookCategoryActionTest extends AbstractTestCase
{
    use PopulateBooksTrait;

    public function testInitialSetupWorksAsExpected()
    {
        $books = $this->populateBooks();
        $this->assertDatabaseCount('categories', 0);

        foreach ($books as $book) {
            $this->assertCount(0, $book->categories);
        }

        $category = new Category();
        $category->title = 'new category';
        $category->save();

        $this->assertDatabaseCount('categories', 1);
        foreach ($books as $book) {
            $this->assertCount(0, $book->categories);
        }
    }

    public function _testUnsupportedOperationThrowsException()
    {
        $request = $this->createJsonRequest('POST', '/api/category/manage');
        $response = $this->app->handle($request);

        $content = (string)$response->getBody();
        $this->assertSame(500, $response->getStatusCode());
        $this->assertStringContainsString('Unsupported operation!', $content);
    }

    public function testAddCategory()
    {
        $category = new Category();
        $category->title = 'new category';
        $category->save();

        $this->assertDatabaseCount('categories', 1);

        $request = $this->createJsonRequest('POST', '/api/category/manage', [
            'oper' => 'add',
            'title' => 'new category 2'
        ]);

        $response = $this->app->handle($request);

        $content = (string)$response->getBody();
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('', $content);
        $this->assertDatabaseCount('categories', 2);
        $this->assertDatabaseHas('categories', ['title' => $category->title]);
        $this->assertDatabaseHas('categories', ['title' => 'new category 2']);
    }

    public function testAddCategory_TitleIsRequired()
    {
        $category = new Category();
        $category->title = 'new category';
        $category->save();

        $this->assertDatabaseCount('categories', 1);

        $request = $this->createJsonRequest('POST', '/api/category/manage', [
            'oper' => 'add',
        ]);

        $response = $this->app->handle($request);
        $content = (string)$response->getBody();
        $this->assertSame(422, $response->getStatusCode());
        $this->assertJsonData(['title' => ['validation.required']], $response); // TODO: fix validation text
        $this->assertDatabaseCount('categories', 1);
        $this->assertDatabaseHas('categories', ['title' => $category->title]);
    }

    public function testDeleteCategory_IdIsRequired()
    {
        $category = new Category();
        $category->title = 'new category';
        $category->save();

        $this->assertDatabaseCount('categories', 1);

        $request = $this->createJsonRequest('POST', '/api/category/manage', [
            'oper' => 'del',
            'title' => 'new category 2'
        ]);

        $response = $this->app->handle($request);

        $content = (string)$response->getBody();
        $this->assertSame(422, $response->getStatusCode());
        $this->assertJsonData(['id' => ['validation.required']], $response); // TODO: fix validation text
        $this->assertDatabaseCount('categories', 1);
        $this->assertDatabaseHas('categories', ['title' => $category->title]);
    }

    public function testDeleteCategory_CategoryDoesNotExist()
    {
        $category = new Category();
        $category->title = 'new category';
        $category->save();

        $this->assertDatabaseCount('categories', 1);

        $request = $this->createJsonRequest('POST', '/api/category/manage', [
            'oper' => 'del',
            'id' => 'unknown-id'
        ]);

        $response = $this->app->handle($request);

        $content = (string)$response->getBody();
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('', $content);
        $this->assertDatabaseCount('categories', 1);
        $this->assertDatabaseHas('categories', ['title' => $category->title]);
    }

    public function testDeleteCategory()
    {
        $category = new Category();
        $category->title = 'new category';
        $category->save();

        $category2 = new Category();
        $category2->title = 'new category 2';
        $category2->save();

        $this->assertDatabaseCount('categories', 2);
        $this->assertDatabaseHas('categories', ['title' => $category->title]);
        $this->assertDatabaseHas('categories', ['title' => $category2->title]);

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
        $category = new Category();
        $category->title = 'new category';
        $category->save();

        $category2 = new Category();
        $category2->title = 'new category 2';
        $category2->save();

        $this->assertDatabaseCount('categories', 2);
        $this->assertDatabaseHas('categories', ['title' => $category->title]);
        $this->assertDatabaseHas('categories', ['title' => $category2->title]);

        $request = $this->createJsonRequest('POST', '/api/category/manage', [
            'oper' => 'edit',
            'id' => $category->guid
        ]);

        $response = $this->app->handle($request);

        $content = (string)$response->getBody();
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('', $content);
        $this->assertDatabaseCount('categories', 2);
        $this->assertDatabaseHas('categories', ['title' => $category->title]);
        $this->assertDatabaseHas('categories', ['title' => $category2->title]);
    }

    public function testEditCategory_ChangeTitle()
    {
        $category = new Category();
        $category->title = 'new category';
        $category->save();

        $category2 = new Category();
        $category2->title = 'new category 2';
        $category2->save();

        $this->assertDatabaseCount('categories', 2);
        $this->assertDatabaseHas('categories', ['title' => $category->title]);
        $this->assertDatabaseHas('categories', ['title' => $category2->title]);

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
        $category = new Category();
        $category->title = 'new category';
        $category->save();

        $category2 = new Category();
        $category2->title = 'new category 2';
        $category2->save();

        $books = $this->populateBooks();
        $bookWithMarker = $books[0];

        $this->assertDatabaseCount('categories', 2);
        $this->assertDatabaseHas('categories', ['title' => $category->title]);
        $this->assertDatabaseHas('categories', ['title' => $category2->title]);
        $this->assertDatabaseCount('books_categories', 0);
        $this->assertCount(0, $bookWithMarker->categories);

        $request = $this->createJsonRequest('POST', '/api/category/manage', [
            'oper' => 'edit',
            'id' => $category->guid,
            'nodeid' => $bookWithMarker->book_guid,
            'marker' => 'true'
        ]);

        $response = $this->app->handle($request);

        $content = (string)$response->getBody();
        $this->assertSame(422, $response->getStatusCode());
        $this->assertJsonData(['marker' => ['validation.boolean']], $response);
        $this->assertDatabaseCount('categories', 2);
        $this->assertDatabaseHas('categories', ['guid' => $category->guid, 'title' => $category->title]);
        $this->assertDatabaseHas('categories', ['guid' => $category2->guid, 'title' => $category2->title]);
        $this->assertDatabaseCount('books_categories', 0);
    }

    public function testEditCategory_CanChangeBookMarkerAndUpdateCategoryTitle()
    {
        $category = new Category();
        $category->title = 'new category';
        $category->save();

        $category2 = new Category();
        $category2->title = 'new category 2';
        $category2->save();

        $books = $this->populateBooks();
        $bookWithMarker = $books[0];

        $this->assertDatabaseCount('categories', 2);
        $this->assertDatabaseHas('categories', ['title' => $category->title]);
        $this->assertDatabaseHas('categories', ['title' => $category2->title]);
        $this->assertDatabaseCount('books_categories', 0);
        $this->assertCount(0, $bookWithMarker->categories);

        $request = $this->createJsonRequest('POST', '/api/category/manage', [
            'oper' => 'edit',
            'id' => $category->guid,
            'title' => 'updated title',
            'nodeid' => $bookWithMarker->book_guid,
            'marker' => true
        ]);

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

    public function testEditCategory_BookMarkerIsRequiredWithNodeId()
    {
        $category = new Category();
        $category->title = 'new category';
        $category->save();

        $category2 = new Category();
        $category2->title = 'new category 2';
        $category2->save();

        $books = $this->populateBooks();
        $bookWithMarker = $books[0];

        $this->assertDatabaseCount('categories', 2);
        $this->assertDatabaseHas('categories', ['title' => $category->title]);
        $this->assertDatabaseHas('categories', ['title' => $category2->title]);
        $this->assertDatabaseCount('books_categories', 0);
        $this->assertCount(0, $bookWithMarker->categories);

        $request = $this->createJsonRequest('POST', '/api/category/manage', [
            'oper' => 'edit',
            'id' => $category->guid,
            'title' => 'updated title',
            'nodeid' => $bookWithMarker->book_guid
        ]);

        $response = $this->app->handle($request);

        $content = (string)$response->getBody();
        $this->assertSame(422, $response->getStatusCode());
        $this->assertJsonData(['marker' => ['validation.required_with']], $response);
        $this->assertDatabaseCount('categories', 2);
        $this->assertDatabaseHas('categories', ['guid' => $category->guid, 'title' => $category->title]);
        $this->assertDatabaseHas('categories', ['guid' => $category2->guid, 'title' => $category2->title]);
    }


}
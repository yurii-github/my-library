<?php

namespace Tests\Functional;

use App\Configuration\Configuration;
use Illuminate\Support\Carbon;
use Tests\PopulateBooksTrait;

class ManageBookActionTest extends AbstractTestCase
{
    use PopulateBooksTrait;


    public function testAddBook()
    {
        $this->setBookLibrarySync(false);
        Carbon::setTestNow(Carbon::now());
        $request = $this->createJsonRequest('POST', '/api/book/manage', [
            'title' => 'title book #1',
            'read' => 'no',
            'favorite' => 0,
            'oper' => 'add'
        ]);

        $response = $this->app->handle($request);

        $this->assertSame(200, $response->getStatusCode());
        $content = (string)$response->getBody();
        $this->assertNotEmpty($content);
        $content = json_decode($content, true);
        $this->assertArrayHasKey('book_guid', $content);
        $this->assertDatabaseCount('books', 1);
        $this->assertDatabaseHas('books', [
            'book_guid' => $content['book_guid'],
            'title' => 'title book #1',
            'created_date' => Carbon::now()->toDateTimeString(),
            'updated_date' => Carbon::now()->toDateTimeString(),
            'book_cover' => null,
            'favorite' => 0,
            'read' => 'no',
            'year' => null,
            'isbn13' => null,
            'author' => null,
            'publisher' => null,
            'ext' => null,
            'filename' => ", ''title book #1'',  []."
        ]);
        $this->assertTrue(
            (bool)preg_match('/[A-Z0-9]{8}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{12}/',
                $content['book_guid']),
            "book_guid '{$content['book_guid']}' is in wrong format"
        );
    }


    public function testBookDelete()
    {
        $this->setBookLibrarySync(false); //TODO: remove file if sync is ON
        $books = $this->populateBooks();
        $book = $books[0];

        $request = $this->createJsonRequest('POST', '/api/book/manage', [
            'id' => $book->book_guid,
            'oper' => 'del'
        ]);
        $response = $this->app->handle($request);
        $this->assertSame('', (string)$response->getBody());

        $this->assertDatabaseCount('books', 2);
        $this->assertDatabaseMissing('books', ['book_guid' => $book->book_guid]);
    }

    
    public function testChangeFilenameIsSkipped()
    {
        $createdAt = Carbon::now();
        $updatedAt = Carbon::now()->copy()->addDay();
        Carbon::setTestNow($createdAt);
        $this->setBookLibrarySync(false);
        $books = $this->populateBooks();
        $book = $books[0];

        Carbon::setTestNow($updatedAt);
        $request = $this->createJsonRequest('POST', '/api/book/manage', [
            'id' => $book->book_guid,
            'filename' => 'some-new-filename.pdf',
            'oper' => 'edit'
        ]);
        $response = $this->app->handle($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('', (string)$response->getBody());
        $this->assertDatabaseHas('books', [
            'book_guid' => $book->book_guid,
            'title' => $book->title,
            'filename' => $book->filename,
            'created_date' => $createdAt->toDateTimeString(),
            'updated_date' => $createdAt->toDateTimeString()
        ]);
    }


    public function testCanChangeFilenameFromTitleWithoutFileWithoutSync()
    {
        $this->setBookLibrarySync(false); // TODO: rename file if sync is ON
        $books = $this->populateBooks();
        $book = $books[0];

        $request = $this->createJsonRequest('POST', '/api/book/manage', [
            'id' => $book->book_guid,
            'title' => 'new title X',
            'oper' => 'edit'
        ]);

        $response = $this->app->handle($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('', (string)$response->getBody());
        $this->assertDatabaseHas('books', [
            'book_guid' => $book->book_guid,
            'title' => 'new title X',
            'filename' => ", ''new title X'',  []."
        ]);
    }

    
    public function testCannotChangeFilenameFromTitleWithoutFileWithSync()
    {
        $this->setBookLibrarySync(true);
        $books = $this->populateBooks();
        $book = $books[0];

        $request = $this->createJsonRequest('POST', '/api/book/manage', [
            'id' => $book->book_guid,
            'title' => 'new title X',
            'oper' => 'edit'
        ]);

        $response = $this->app->handle($request);
        $this->assertSame(400, $response->getStatusCode());
        $this->assertJsonData([
            'error' => "Sync for file failed. Source file 'vfs://base/data/books/, ''title book #1'',  [].' does not exist"
        ], $response);
        $this->assertDatabaseHas('books', [
            'book_guid' => $book->book_guid,
            'title' => $book->title,
            'filename' => $book->filename
        ]);
    }
    
    
    /**
     * @dataProvider provideSyncModes
     *
     * ACTION MUST:
     *
     * 1. not allow changes of book_guid, created and updated date, filename
     * 2. generate filename based on title
     * 3. generate updated_date
     * 4. 
     */
    public function __test_action_Manage_Edit(bool $syncMode)
    {
        $this->setBookLibrarySync($syncMode);
        $books = $this->populateBooks();
        $book = $books[0];

        Carbon::setTestNow(Carbon::now());
        $request = $this->createJsonRequest('POST', '/api/book/manage', [
            'title' => 'title book #1',
            'read' => 'no',
            'favorite' => 0,
            'oper' => 'edit'
        ]);
        $response = $this->app->handle($request);


        // CONFIGURE
        $book = $this->books['inserted'][0];
        $book_expected = $this->books['expected'][0];

        $filename_expected = $filename_old = \Yii::$app->mycfg->library->directory . $book['filename'];
        file_put_contents($filename_expected, 'sample-data');

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['oper'] = 'edit';
        $_POST['id'] = $book['book_guid'];
        $_POST['created_date'] = '2000-01-01';
        $_POST['updated_date'] = '2000-01-01';
        $_POST['filename'] = '2000-01-01';

        // - - - - - -


        $this->controllerApiBook->runAction('manage');
        $book_expected['filename'] = ", ''title book #1'',  [].";

        // #1
        // WORKAROUND FOR TRAVIS
        $dt = new \DateTime();
        $dt->setTimezone(new \DateTimeZone(\Yii::$app->getTimeZone()));
        $book_expected['updated_date'] = $dt->format('Y-m-d H:i:s');

        //CHECKING
        /* @var $book_current \yii\db\BaseActiveRecord */
        $book_current = Books::findOne(['book_guid' => $book['book_guid']]);

        // #2
        // WORKAROUND FOR TRAVIS: remove seconds, as it fails on slow machines, definely fails on Travis
        $book_expected['updated_date'] = (new \DateTime($book_expected['updated_date']))->format('Y-m-d H:i');
        $book_current['updated_date'] = (new \DateTime($book_current['updated_date']))->format('Y-m-d H:i');

        // #3
        $book_current_arr = $book_current->getAttributes();
        $keys = array_keys($book_expected);
        foreach ($keys as $k) {
            if ($k == 'filename') { // skip filename checks here. checked at #4 below
                continue;
            }
            $this->assertEquals($book_expected[$k], $book_current_arr[$k], "expected '$k' doesn't match");
        }

        // #4
        if ($syncMode) { // file rename if sync ON
            $filename_expected = \Yii::$app->mycfg->library->directory . $book_expected['filename']; // renamed new
            $this->assertFileNotExists($filename_old); // old is not existed
        }
        $this->assertFileExists($filename_expected);
        $this->assertEquals(file_get_contents($filename_expected), 'sample-data');
    }


    public function provideSyncModes()
    {
        return [
            [true], // sync enabled
            [false] // sync disabled
        ];
    }


    protected function setBookLibrarySync(bool $mode): void
    {
        $config = $this->app->getContainer()->get(Configuration::class);
        assert($config instanceof Configuration);
        $config->getLibrary()->sync = $mode;
    }
}
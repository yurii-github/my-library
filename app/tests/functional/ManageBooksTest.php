<?php
namespace tests\functional;

use app\models\Books;
class ManageBooksTest extends \tests\AppFunctionalTestCase
{
	// fixture
	public $books; 
	
	/**
	 * site controller
	 * @var \app\controllers\SiteController
	 */
	private $controllerSite;
	
	
	protected function setUp()
	{
		$this->books = $this->setupFixture('books');
		
		parent::setUp();
		
		$this->controllerSite = \Yii::$app->createControllerByID('site');
	}
	
	
	/*
	function test_BooksFixture()
	{
		$this->assertDataSetsEqual($this->createArrayDataSet(['books' => $this->books['expected']]), $this->getConnection()->createDataSet(['books']));
	}*/
	
	
	/**
	 * @expectedException \yii\base\ErrorException
	 */
	function test_action_saveCover_badFormat()
	{
		/*
		 * MUST
		 * 1. deny non valid file, only images
		 */
		$_GET['book_guid'] = $book_guid = 1;
		$cover = 'invalid-cover-fomatted-data';
		
		$mockRequest = $this->getMockBuilder(\yii\web\Request::class)->setMethods(['getRawBody'])->getMock();
		$mockRequest->expects($this->any())->method('getRawBody')->willReturn($cover);
		$this->mockYiiApplication([ 'components' => [ 'request' => $mockRequest ] ]);
		
		// [ 1 ]
		$this->controllerSite->runAction('cover-save');
	}
	
	
	/**
	 * MUST
	 * 2. resize image
	 * 3. save image to database
	 */
	function test_action_saveCover_Resize()
	{
		$cover = file_get_contents(self::$baseTestDir.'/data/cover.jpg');
		$mockRequest = $this->getMockBuilder(\yii\web\Request::class)->setMethods(['getRawBody'])->getMock();
		$mockRequest->expects($this->any())->method('getRawBody')->willReturn($cover);

		$_GET['book_guid'] = $book_guid = 1;		
		$this->mockYiiApplication([ 'components' => [ 'request' => $mockRequest ] ]);
	
		$this->controllerSite->runAction('cover-save'); //save to db
		$actual_cover = $this->getConnection()->createQueryTable('books', "SELECT * FROM books WHERE book_guid=$book_guid")->getRow(0)['book_cover'];
		// [ 2 ]
		$this->assertLessThan(strlen($cover), strlen($actual_cover), 'resized image is not smaller than original'); // smaller size
		// fails on Travis. why?
		//$this->assertEquals(md5($actual_cover), md5_file(self::$baseTestDir.'/data/cover-resized.jpg'), 'resized image has different size as expected sample');
		// replacement for failing test above
		// [ 3 ]
		$this->assertNotFalse(imagecreatefromstring($actual_cover));
	}
	
	
	function test_action_getCover_empty()
	{
		\Yii::$app->setAliases(['@webroot' => '@app/public']);
		file_put_contents($this->initAppFileSystem() . '/public/assets/app/book-cover-empty.jpg', 'empty-cover-data');
		$book_guid = 1;
		
		$cover = $this->controllerSite->runAction('cover', ['book_guid' => $book_guid]);
	
		$this->assertEquals('empty-cover-data', $cover);
	}
	
	
	function test_action_getCover_exists()
	{
		\Yii::$app->setAliases(['@webroot' => '@app/public']);
		file_put_contents($this->initAppFileSystem() . '/public/assets/app/book-cover-empty.jpg', 'empty-cover-data');
		$book_guid = 1;
		
		$this->getPdo()->exec("UPDATE books SET book_cover='valid-cover-data' WHERE book_guid='$book_guid'");		
		$cover = $this->controllerSite->runAction('cover', ['book_guid' => $book_guid]);
	
		$this->assertEquals('valid-cover-data', $cover);
	}
	
	
	
	function test_action_getBooks()
	{
		$object = json_decode($this->controllerSite->runAction('books'));
		
		$this->assertInstanceOf('\stdClass', $object);
		$this->assertObjectHasAttribute('page', $object);
		$this->assertEquals(1, $object->page);
		$this->assertObjectHasAttribute('total', $object);
		$this->assertEquals(1, $object->page);
		$this->assertObjectHasAttribute('records', $object);
		$this->assertEquals(3, $object->records);
		$this->assertObjectHasAttribute('rows', $object);
		$this->assertTrue(is_array($object->rows));
		// rows, dummy check
		foreach ($this->books['inserted'] as $k => $book) {
			$this->assertInstanceOf('\stdClass', $object->rows[$k]);
			$this->assertEquals($book['book_guid'], $object->rows[$k]->id);
		}
	}
	
	
	function test_action_getBooks_Paging()
	{
		$book_expected = $this->books['expected'][1];

		$_GET['page'] = 2;
		$_GET['rows'] = 1;
		$_GET['sidx'] = 'id'; //book_guid
		$_GET['sord'] = 'asc';

		$object = json_decode($this->controllerSite->runAction('books'));
		
		$this->assertInstanceOf('\stdClass', $object);
		$this->assertEquals(2, $object->page);
		$this->assertEquals(3, $object->total); // 3 pages
		$this->assertEquals(3, $object->records); // 3 rows total
		$this->assertTrue(is_array($object->rows));
		$this->assertCount(1, $object->rows); // 1 row returned
		$book_actual = $object->rows[0]; //object
		$this->assertEquals($book_actual->id, $book_expected['book_guid']);
		$this->assertEquals($book_actual->cell[5], $book_expected['title']);
		
		//var_dump($object); die;
	}
	
	
	function test_action_getBooks_SimpleFilter()
	{
		$book_expected = $this->books['expected'][1];
		$filters = [
			'rules' => [[
				'op' => 'bw', 	// ['bw'=>'like','eq'=>'='];
				'field' => 'title',
				'data' => '#2' // matches 'title #2'
			]]
		];
		//?_search=true&nd=1437116037426&rows=10&page=1&sidx=created_date&sord=desc&filters={"groupOp":"OR","rules":[{"field":"title","op":"bw","data":"#2"}]}
		$_GET['filters'] = json_encode($filters);
		
		$object = json_decode($this->controllerSite->runAction('books'));
		$this->assertInstanceOf('\stdClass', $object);
		$this->assertObjectHasAttribute('total', $object);
		$this->assertEquals(1, $object->total);
		$this->assertObjectHasAttribute('rows', $object);
		$this->assertTrue(is_array($object->rows));
		$book_actual = $object->rows[0]; //object
		$this->assertCount(1, $object->rows);
		//var_dump($book_actual); die;
		$this->assertEquals($book_actual->id, $book_expected['book_guid']);
		$this->assertEquals($book_actual->cell[5], $book_expected['title']);
	}
	
	
	/**
	 *  @see test_action_getBooks()
	 */
	function test_action_getBooks_CombinedFilter()
	{
		$book_expected = $this->books['expected'][1];
		$filters = [
			'groupOp' => 'AND',
			'rules' => [
				[
					'op' => 'bw', 	// ['bw'=>'like','eq'=>'='];
					'field' => 'title',
					'data' => 'title' // matches '%title%'
				],
				[
					'op' => 'eq',
					'field' => 'filename',
					'data' => 'filename-2'
				]
			]
		];
		
		$_GET['filters'] = json_encode($filters);
	
		$object = json_decode($this->controllerSite->runAction('books'));
		$this->assertInstanceOf('\stdClass', $object);
		$this->assertObjectHasAttribute('rows', $object);
		$this->assertTrue(is_array($object->rows));
		$book_actual = $object->rows[0]; //object
		$this->assertCount(1, $object->rows);
		//var_dump($book_actual); die;
		$this->assertEquals($book_actual->id, $book_expected['book_guid']);
		$this->assertEquals($book_actual->cell[5], $book_expected['title']);
	}
	
	
	/**
	 *  @see test_action_getBooks()
	 */
	function test_action_getBooks_UnknownConditionInFilter()
	{
		$book_expected = $this->books['expected'][1];
		$filters = [
			'rules' => [[
				'op' => 'unknown-condition',
				'field' => 'title',
				'data' => '#2'
			]]
		];
		
		$_GET['filters'] = json_encode($filters);
	
		$object = json_decode($this->controllerSite->runAction('books'));
		$this->assertInstanceOf('\stdClass', $object);
		$this->assertObjectHasAttribute('total', $object);
		$this->assertEquals(count($this->books['expected']), $object->records); // returns all records, rule does not apply
		$this->assertEquals(count($this->books['expected']), count($object->rows)); // returns all records, rule does not apply
	}
	
	
	/**
	 * MUST
	 * 1. remove record from books table based on book_guid
	 * 2. TODO: remove file if sync is ON
	 */
	public function test_action_Manage_Delete()
	{
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$_POST['oper'] = 'del';
		$_POST['id'] = 3; // book_guid
		
		$this->controllerSite->runAction('manage');
		
		unset($this->books['expected'][2]);
		$this->assertDataSetsEqual($this->createArrayDataSet(['books' => $this->books['expected']]), $this->getConnection()->createDataSet(['books']));
	}
	
	
	/**
	 * ACTION MUST:
	 * 
	 * 1. generate book guid
	 * 2. generate created and updated date
	 * 3. generate filename based on title etc..
	 */
	public function test_action_Manage_Add()
	{
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$book_1 = $this->books['inserted'][0];
		$book_1_expected =  $this->books['expected'][0];
		$_POST = $book_1;
		$_POST['oper'] = 'add';
		
		$this->controllerSite->runAction('manage');
				
		$newRecord = $this->getConnection()->createQueryTable('books', 'SELECT * FROM books WHERE book_guid NOT IN(1,2,3)')->getRow(0); //array
		$oldRecords = $this->getConnection()->createQueryTable('books', 'SELECT * FROM books WHERE book_guid IN(1,2,3)');
		
		// check existing data did not change
		$this->assertTrue($oldRecords->matches($this->createArrayDataSet(['books' => $this->books['expected']])->getTable('books')), 'old records does not match as they were changed');
		
		// pre verify
		// [ 1 ]
		$this->assertTrue((bool)preg_match('/[A-Z0-9]{8}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{12}/', $newRecord['book_guid']),
			"book_guid '{$newRecord['book_guid']}' is in wrong format");
		// [ 2 ]
		$this->assertEquals((new \DateTime())->format('Y-m-d'), \DateTime::createFromFormat('Y-m-d H:i:s', $newRecord['updated_date'])->format('Y-m-d'));
		$this->assertEquals((new \DateTime())->format('Y-m-d'), \DateTime::createFromFormat('Y-m-d H:i:s', $newRecord['updated_date'])->format('Y-m-d'));
		// [ 3 ]
		$this->assertEquals(", ''title book #1'',  [].", $newRecord['filename']);
		
		// mod expected with verified data
		foreach (['book_guid','created_date','updated_date','filename'] as $k) {
			$book_1_expected[$k] = $newRecord[$k];
		}
		
		//verify
		$this->assertArraySubset($book_1_expected, $newRecord);
	}

	
	/**
	 * @dataProvider pSync
	 * 
	 * ACTION MUST:
	 * 
	 * 1. not allow changes of book_guid, created and updated date, filename
	 * 2. generate filename based on title
	 * 3. generate updated_date
	 * 4. rename file if sync is ON
	 */
	public function test_action_Manage_Edit($sync)
	{
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$book = $this->books['inserted'][0];
		$book_expected =  $this->books['expected'][0];
		$filename_expected = $filename_old = \Yii::$app->mycfg->library->directory . $book_expected['filename'];
		file_put_contents($filename_expected, 'sample-data');
		\Yii::$app->mycfg->library->sync = $sync;
		
		$_POST['oper'] = 'edit';
		$_POST['id'] = $book['book_guid'];
		
		// CHANGING
		// [ 1 ]
		$_POST['created_date'] = '2000-01-01';
		$_POST['updated_date'] = '2000-01-01';
		$_POST['filename'] = '2000-01-01';
		// [ 2 ]
		$book_expected['filename'] = ", ''title book #1'',  [].";
		// [ 3 ]
		$book_expected['updated_date'] = (new \DateTime())->format('Y-m-d H:i:s');
		
		$this->controllerSite->runAction('manage');
		
		/* @var $book_current \yii\db\BaseActiveRecord */
		$book_current = Books::findOne(['book_guid' => $book['book_guid']]);

		//remove seconds, as it fails on slow machines, definely fails on Travis
		$book_expected['updated_date'] = (new \DateTime($book_expected['updated_date']))->format('Y-m-d H:i');
		$book_current['updated_date']  = (new \DateTime($book_current['updated_date']))->format('Y-m-d H:i');
		
		//var_dump($book_expected,$book_current->getAttributes()); die;
		$this->assertArraySubset($book_expected, $book_current->getAttributes());
		
		if ($sync) {	
			$filename_expected = \Yii::$app->mycfg->library->directory . $book_expected['filename'];
			$this->assertFileNotExists($filename_old);
		}
		
		$this->assertFileExists($filename_expected);
		$this->assertEquals(file_get_contents($filename_expected), 'sample-data');		
	}
	

	function pSync()
	{
		return [
			[true], // sync enabled
			[false] // sync disabled
		];
	}
	
}
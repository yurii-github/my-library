<?php
namespace tests\models;

use app\models\Books;
use app\components\Configuration;

class BooksTest extends \tests\AppTestCase
{
	protected $tbname = 'books';
	
	protected function setUp()
	{
		$this->mockYiiApplication([
			'components' => [
				'mycfg' => new Configuration(['config_file' => $this->getConfigFilename()])
			]
		]);
		
		$this->dataset = [
			$this->tbname => [  
				[
					'book_guid' => 1,
					'created_date' => '2014-01-01 00:00:00',
					'updated_date' => '2014-01-01 00:00:00',
					'favorite' => 1,
					'title' => 'title book #1',
					'filename' => 'filename-1',
				],
				[
					'book_guid' => 2, 
					'created_date' => '2014-01-01 00:00:00', 
					'updated_date' => '2014-01-01 00:00:00', 
					'favorite' => 1, 
					'title' => 'title book #2', 
					'filename' => 'filename-2',
				]
			]//tbl books
		];
		
		
		parent::setUp();
	}
	
	public function pSync()
	{
		return [
			// sync option ON/OFF
			[true],	[false]
		];
	}

	
	/**
	 * @dataProvider pSync
	 * @param bool $sync
	 * @param bool $book_exists
	 */
	public function test_Update($sync)
	{
		$current = $this->getConnection()->createDataSet([$this->tbname]);
		
		
		$book = Books::findOne(['book_guid' => 1]);
		$book_filename = \Yii::$app->mycfg->library->directory.$book->filename;
		file_put_contents($book_filename, 'something');
		
		
		$book->title = 'xxx';
		\Yii::$app->mycfg->library->sync = $sync;
		$book->save();

		$new_filename = \Yii::$app->mycfg->library->directory.$book->filename;
		
		// rename check
		if ($sync) { //ON
			
			$this->assertTrue(file_exists($new_filename), 'SYNC ON: no new file. rename failed');
			$this->assertFalse(file_exists($book_filename), 'SYNC ON: old file not removed. rename failed');
			$this->assertEquals('something', file_get_contents($new_filename));
		} else { //OFF
			$this->assertFalse(file_exists($new_filename), 'SYNC OFF: new file created. file renamed. must not occur');
			$this->assertTrue(file_exists($book_filename), 'SYNC OFF: old file removed. file renamed. must not occur.');
			$this->assertEquals('something', file_get_contents($book_filename));
		}

		//TODO: db check
	
	}
	
	
	/**
	 * @dataProvider pSync
	 * @param bool $sync
	 * @param bool $book_exists
	 */
	public function test_Delete($sync)
	{

		
		$book_delete = $this->dataset[$this->tbname][0];
		$book__left = $this->dataset[$this->tbname][1];
		
		$expected_ds = [
			$this->tbname=> [[
				'book_guid' => $book__left['book_guid'],
				'created_date' => $book__left['created_date'],
				'updated_date' => $book__left['updated_date'],
				'book_cover' => null,
				'favorite' => $book__left['favorite'],
				'read' => 'no',
				'year' => null,
				'title' => $book__left['title'],
				'isbn13' => null,
				'author' => null,
				'publisher' => null,
				'ext' => null,
				'filename' => $book__left['filename']
			]]
		];
		
		//prepare
		$book = Books::findOne(['book_guid' => $book_delete['book_guid']]);
		$book_delete_filename = \Yii::getAlias('@app/data/books/').$book_delete['filename'];
		file_put_contents($book_delete_filename, 'something');
		\Yii::$app->mycfg->library->sync = $sync;
		
		$book->delete(); //act

		//check
		if ($sync) {
			$this->assertFalse(file_exists($book_delete_filename), "Sync ON. book '{$book_delete_filename}' was not deleted");
		} else {
			$this->assertTrue(file_exists($book_delete_filename), "Sync OFF. book '{$book_delete_filename}' was deleted");
		}
		
		$this->assertDataSetsEqual($this->createArrayDataSet($expected_ds), $this->getConnection()->createDataSet([$this->tbname]));
	}
	
	
	function test_jgridBooks()
	{	
		//OK
		$get = ['page' => 1,'limit' => 10, 'sort_column' => 'created_date','sort_order'=> 'desc', 'filters' => '' ];
		$resp = Books::jgridBooks($get);
		$this->assertInstanceOf('\stdClass', $resp);
		$this->assertEquals($resp->page, 1);
		$this->assertEquals($resp->records, count($this->dataset[$this->tbname]));
		$this->assertEquals(count($resp->rows), $resp->records);
		$book1 = $resp->rows[0];
		$this->assertEquals(true, is_array($book1));
		$this->assertEquals($this->dataset[$this->tbname][0]['book_guid'], $book1['id']);
		$this->assertEquals( (new \DateTime($this->dataset[$this->tbname][0]['created_date']))->format('d-m-Y'), $book1['cell'][0]);
		//TODO: more stuff?
		
		// empty get, test defaults
		unset($get['page']);
		$resp = Books::jgridBooks([]);
		$this->assertInstanceOf('\stdClass', $resp);
		$this->assertEquals($resp->page, 1);
		$this->assertEquals($resp->records, count($this->dataset[$this->tbname]));
	}
	
	
}



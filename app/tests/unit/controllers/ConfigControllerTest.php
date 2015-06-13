<?php
namespace tests\controllers;

use app\controllers\ConfigController;
use app\components\Configuration;
use org\bovigo\vfs\vfsStream;

class ConfigControllerTest extends \tests\AppTestCase
{

	private $cfg_file = 'config/libconfig.json'; //rel to @app
	
	protected function setUp()
	{
		$this->mockYiiApplication();
		
		$this->dataset = [
			'books' =>  [
				//db only
				['book_guid' => 1, 'filename' => 'file-1',  'created_date' => '2014-01-01 00:00:00', 'updated_date' => '2014-01-01 00:00:00'],
				['book_guid' => 2, 'filename' => 'file-2', 'created_date' => '2014-01-01 00:00:00', 'updated_date' => '2014-01-01 00:00:00'],
				// db and fs
				['book_guid' => 3, 'filename' => 'file-3', 'created_date' => '2014-01-01 00:00:00', 'updated_date' => '2014-01-01 00:00:00',  ]
			]//tbl books
		];

		file_put_contents(vfsStream::url('base/data/books/file-3'), 'some data'); // db and fs
		file_put_contents(vfsStream::url('base/data/books/file-4'), 'some data'); //fs only

		parent::setUp();
	}
	
	// values that set by database on insert
	protected function defaultRowValues()
	{
		return [
			'book_guid' => '?', // for dataset order, must be set
			'created_date' => '?',
			'updated_date' => '?',
			'book_cover' => null,
			'favorite' => 0.0,
			'read' => 'no',
			'year' => null,
			'title' => null,
			'isbn13' => null,
			'author' => null,
			'publisher' => null,
			'ext' => null,
			'filename' => '?'
		];		
	}
	
	protected function getDatasetAfterClearDbFiles()
	{
		$full_row = array_merge( $this->defaultRowValues(), $this->dataset['books'][2]);
		return $this->createArrayDataSet(['books' => [ $full_row ]]);
	}
	
	public function test_getLibraryBookFilenames()
	{
		$c = new ConfigController('config', \Yii::$app);
		$resp = json_decode($c->actionCheckFiles());

		$this->assertEquals(2, count($resp->db), 'db only records does not match');
		$this->assertArraySubset(['file-1','file-2'], $resp->db, 'filename of db only files does not match');
		$this->assertEquals(1, count($resp->fs), 'file system only file count does not match');
		$this->assertEquals('file-4', $resp->fs[0], 'filename of file system only file does not match');
	}
	
	
	public function test_actionClearDbFiles()
	{
		$c = new ConfigController('config', \Yii::$app);
		
		// db only records cont
		$_GET['count'] = 'all';
		$resp = json_decode($c->actionClearDbFiles());
		
		$this->assertEquals(2, $resp);
		
		// db cleared files that are not if fs, file-3 left only
		unset($_GET['count']);
		$resp = json_decode($c->actionClearDbFiles());
		
		$this->assertArraySubset([1,2], $resp);//removed ids
		$this->assertEquals(1, $this->getConnection()->getRowCount('books'));
		$this->assertDataSetsEqual($this->getDatasetAfterClearDbFiles(),  $this->getConnection()->createDataSet(['books']), 'file-3 was not left in db');		
	}
	
	public function test_actionImportFiles()
	{
		$c = new ConfigController('config', \Yii::$app);
		
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$_POST['post'] = ['file-4'];
		$resp = json_decode($c->actionImportFiles());
		$this->assertArraySubset($_POST['post'], $resp->data);
		$this->assertTrue($resp->result);
	}
	
	
}




<?php
namespace tests\models;

use app\models\Books;

class BooksTest extends \tests\AppTestCase
{
	protected function setUp()
	{

		$this->dataset = ['books' =>  [  
				['book_guid' => 1, 'created_date' => '2014-01-01 00:00:00', 'updated_date' => '2014-01-01 00:00:00', 'favorite' => 1, 'title' => 'title book #1'],
				['book_guid' => 2, 'created_date' => '2014-01-01 00:00:00', 'updated_date' => '2014-01-01 00:00:00', 'favorite' => 1, 'title' => 'title book #2']
			]//tbl books
		];
		parent::setUp();
	}

	
	function test_jgridBooks()
	{
		$this->mockYiiApplication();
		
		//OK
		$get = ['page' => 1,'limit' => 10, 'sort_column' => 'created_date','sort_order'=> 'desc', 'filters' => '' ];
		$resp = Books::jgridBooks($get);
		$this->assertInstanceOf('\stdClass', $resp);
		$this->assertEquals($resp->page, 1);
		$this->assertEquals($resp->records, count($this->dataset['books']));
		$this->assertEquals(count($resp->rows), $resp->records);
		$book1 = $resp->rows[0];
		$this->assertEquals(true, is_array($book1));
		$this->assertEquals($this->dataset['books'][0]['book_guid'], $book1['id']);
		$this->assertEquals( (new \DateTime($this->dataset['books'][0]['created_date']))->format('d-m-Y'), $book1['cell'][0]);
		//TODO: more stuff?
		
		// empty get, test defaults
		unset($get['page']);
		$resp = Books::jgridBooks([]);
		$this->assertInstanceOf('\stdClass', $resp);
		$this->assertEquals($resp->page, 1);
		$this->assertEquals($resp->records, count($this->dataset['books']));
	}
	
	
}



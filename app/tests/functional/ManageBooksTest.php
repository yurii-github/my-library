<?php
namespace tests\functional;

class ManageBooksTest extends \tests\AppFunctionalTestCase
{
	// fixture
	public $books; 
	
	protected function setUp()
	{
		$this->books = $this->setupFixture('books');		
		parent::setUp();
	}
	
	function test_BooksFixture()
	{
		$this->assertDataSetsEqual($this->createArrayDataSet(['books' => $this->books['expected']]), $this->getConnection()->createDataSet(['books']));
	}
	
	
	
	function test_Site_actionBooks()
	{
		/* @var $controller \app\controllers\SiteController */
		$controller = \Yii::$app->createControllerByID('site');
			
		$json = $controller->actionBooks();
		$object = json_decode($json);
		
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
		foreach ($this->books['insert'] as $k => $book) {
			$this->assertInstanceOf('\stdClass', $object->rows[$k]);
			$this->assertEquals($book['book_guid'], $object->rows[$k]->id);
		}
		
		//TODO: filters, page etc
		
/*
 * 				$data = [
					'page' => \Yii::$app->request->get('page'),
					'limit' => \Yii::$app->request->get('rows'),
					'filters' => \Yii::$app->request->get('filters'),
					'sort_column' => \Yii::$app->request->get('sidx'),
					'sort_order'=> \Yii::$app->request->get('sord'),
				];
 */
	
	}
}
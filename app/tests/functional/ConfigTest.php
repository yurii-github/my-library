<?php
namespace tests\functional;

use app\controllers\ConfigController;
use org\bovigo\vfs\vfsStreamDirectory;

class mockConfigController extends ConfigController
{
	protected function getFiles_FileSystemOnly()
	{
		
	}
}

class ConfigTest extends \tests\AppFunctionalTestCase
{
	private $books; //fixture
	
	protected function setUp()
	{
		file_put_contents($this->initAppFileSystem() .'/data/books/filename-3', 'some data'); // db and fs
		file_put_contents($this->initAppFileSystem() .'/data/books/filename-4', 'some data'); //fs only
		
		$this->books = $this->setupFixture('books');
		parent::setUp();
	}

	
	public function test_getLibraryBookFilenames()
	{
		$c = \Yii::$app->createControllerByID('config');
		//$c = new ConfigController('config', \Yii::$app);
		$resp = json_decode($c->actionCheckFiles());

		$this->assertEquals(2, count($resp->db), 'db only records does not match');
		$this->assertArraySubset(['filename-1','filename-2'], $resp->db, 'filename of db only files does not match');
		$this->assertEquals(1, count($resp->fs), 'file system only file count does not match');
		$this->assertEquals('filename-4', $resp->fs[0], 'filename of file system only file does not match');
	}


	public function test_actionClearDbFiles_recordsCount()
	{
		$_GET['count'] = 'all';
		
		$c = \Yii::$app->createControllerByID('config');
		//$c = new ConfigController('config', \Yii::$app);
		$resp = json_decode($c->actionClearDbFiles()); // db only records cont
		
		$this->assertEquals(2, $resp);
	}
	

	public function test_actionClearDbFiles_clearDb()
	{
		// db cleared files that are not if fs, filename-3 left only
		$c = new ConfigController('config', \Yii::$app);
		$resp = json_decode($c->actionClearDbFiles());
	
		$this->assertArraySubset([1,2], $resp);//removed ids
		$this->assertEquals(1, $this->getConnection()->getRowCount('books'));
		$this->assertDataSetsEqual($this->createArrayDataSet(
			['books' => [$this->books['expected'][2]]]),
			$this->getConnection()->createDataSet(['books']), 'filename-3 was not left in db');
	}
	

	public function test_actionImportFiles()
	{
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$_POST['post'] = ['filename-4'];
		
		$c = new ConfigController('config', \Yii::$app);
		$resp = json_decode($c->actionImportFiles());
		
		$this->assertArraySubset($_POST['post'], $resp->data);
		$this->assertTrue($resp->result);
	}


	
	public function test_actionSave()
	{
		/*
		 * MUST
		 * 1. save data to config file
		 * 2. respond with json string
		 */
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$_POST['field'] = 'system_language';
		$_POST['value'] = 'uk-UA';
		
		$c = new ConfigController('config', \Yii::$app);
		$resp = json_decode($c->actionSave());
		
		$this->assertEquals("<b>language</b> was successfully updated", $resp->msg);
		$this->assertTrue($resp->result);
		$this->assertEquals('system', $resp->title);
		
		//TODO: check file change. not really needed as it will be checked configuration unit test
	}
	
	
	public function test_actionSave_noWriteRights()
	{
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$_POST['field'] = 'system_language';
		$_POST['value'] = 'uk-UA';

		chmod(\Yii::$app->mycfg->config_file, 0444);
		
		$c = new ConfigController('config', \Yii::$app);
		$resp = json_decode($c->actionSave());
		
		$this->assertFalse($resp->result);
	}
	
	
}
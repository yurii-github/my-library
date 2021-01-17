<?php
namespace tests\functional;

use app\controllers\ConfigController;
use org\bovigo\vfs\vfsStreamDirectory;


class ConfigTest extends \tests\AppFunctionalTestCase
{
	private $books; //fixture

	/**
	 * config controller
	 * @var \app\controllers\ConfigController
	 */
	private $controller;

	//TODO: name filenames properly not just filename3 etc.
	private $filename_fs_only = 'filename-4';

	protected function setUp(): void
	{

		file_put_contents($this->initAppFileSystem() .'/data/books/filename-3', 'some data'); // db and fs
		file_put_contents($this->initAppFileSystem() ."/data/books/{$this->filename_fs_only}", 'some data'); //fs only
		$this->books = $this->setupFixture('books');

		parent::setUp();

		//init config controller
		$this->controller = \Yii::$app->createControllerByID('config');
	}


	public function test_getLibraryBookFilenames()
	{
		$resp = json_decode($this->controller->runAction('check-files'));

		$this->assertEquals(2, count($resp->db), 'db only records does not match');
		$this->assertArraySubset(['filename-1','filename-2'], $resp->db, 'filename of db only files does not match');
		$this->assertEquals(1, count($resp->fs), 'file system only file count does not match');
		$this->assertEquals($this->filename_fs_only, $resp->fs[0], 'filename of file system only file does not match');
	}


	public function test_actionClearDbFiles_recordsCount()
	{
		$_GET['count'] = 'all';

		$resp = json_decode($this->controller->runAction('clear-db-files')); // db only records count

		$this->assertEquals(2, $resp);
	}


	public function test_actionClearDbFiles_clearDb()
	{
		$resp = json_decode($this->controller->runAction('clear-db-files')); // db cleared files that are not if fs, filename-3 left only

		$this->assertArraySubset([1,2], $resp);//removed ids
		$this->assertEquals(1, $this->getPdo()->query('SELECT * FROM books')->rowCount());

		//TODO: fix test
		$this->assertDataSetsEqual($this->createArrayDataSet(
			['books' => [$this->books['expected'][2]]]),
			$this->getConnection()->createDataSet(['books']), 'filename-3 was not left in db');
	}


	/**
	 * returns list of files available in filesystem only (no database records)
	 */
	public function test_action_ImportFiles_GET()
	{
		$_SERVER['REQUEST_METHOD'] = 'GET';

		$resp = json_decode($this->controller->runAction('import-files'));

		$this->assertNotNull($resp);
		$this->assertCount(1, $resp);
		$this->assertArraySubset([$this->filename_fs_only], $resp);
	}


	/**
	 * import file available in filesystem only (no database records)
	 */
	public function test_action_ImportFiles_POST()
	{
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$_POST['post'] = [$this->filename_fs_only]; //just 1 file

		$resp = json_decode($this->controller->runAction('import-files'));

		$this->assertArraySubset($_POST['post'], $resp->data);
		$this->assertTrue($resp->result);
	}


	public function test_action_ImportFiles_EMPTY()
	{
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$resp = json_decode($this->controller->runAction('import-files'));
		$this->assertEmpty($resp->data);
		$this->assertTrue($resp->result);
	}


	public function test_action_ImportFiles_ERROR()
	{
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$_POST['post'] = [$this->filename_fs_only]; //just 1 file

		try {
			\Yii::$app->db->createCommand("DROP TABLE {{%books}}")->execute();

			$resp = json_decode($this->controller->runAction('import-files'));

			$this->assertEmpty($resp->data);
			$this->assertFalse($resp->result);
			$this->assertEquals("The table does not exist: {{%books}}", $resp->error);

		} finally {
			$this->resetConnection(); // we fail schema, need to recreate it after test
		}

	}


}

<?php
namespace tests\functional;

use app\models\Books;
use app\components\ApcCache;

class ManageBooksTest extends \tests\AppFunctionalTestCase
{


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

		$this->controllerApiBook->runAction('manage');

		unset($this->books['expected'][2]);
		$this->assertDataSetsEqual($this->createArrayDataSet(['books' => $this->books['expected']]), $this->createDataSet(['books']));
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

		$this->controllerApiBook->runAction('manage');

		$newRecord = $this->getPdo()->prepare('SELECT * FROM books WHERE book_guid NOT IN(1,2,3)')->fetch();
		$oldRecords = $this->getPdo()->prepare('SELECT * FROM books WHERE book_guid IN(1,2,3)')->fetchAll();

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
		// CONFIGURE
		$book = $this->books['inserted'][0];
		$book_expected =  $this->books['expected'][0];

		$filename_expected = $filename_old = \Yii::$app->mycfg->library->directory . $book['filename'];
		file_put_contents($filename_expected, 'sample-data');

		$_SERVER['REQUEST_METHOD'] = 'POST';
		$_POST['oper'] = 'edit';
		$_POST['id'] = $book['book_guid'];
		$_POST['created_date'] = '2000-01-01';
		$_POST['updated_date'] = '2000-01-01';
		$_POST['filename'] = '2000-01-01';

		\Yii::$app->mycfg->library->sync = $sync;
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
		$book_current['updated_date']  = (new \DateTime($book_current['updated_date']))->format('Y-m-d H:i');

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
		if ($sync) { // file rename if sync ON
			$filename_expected = \Yii::$app->mycfg->library->directory . $book_expected['filename']; // renamed new
			$this->assertFileNotExists($filename_old); // old is not existed
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

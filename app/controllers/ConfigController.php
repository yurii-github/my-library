<?php
namespace app\controllers;

use Yii;
use app\models\Books;
use app\models\Helper;
use yii\web\HttpException;
use app\components\Controller;
use app\models\Users;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

class ConfigController extends Controller
{
	/**
	 * returns array of books filenames located in FS library folder
	 * filename is in UTF-8
	 */
	private function getLibraryBookFilenames()
	{
		$files = [];
		try {
			$libDir = new \DirectoryIterator(\Yii::$app->mycfg->library->directory);
			foreach ($libDir as $file) {
				if ($file->isFile()) {
					$files[] = \Yii::$app->mycfg->Decode($file->getFilename());
				}
			}
		}
		finally {//suppress any errors
			if (!is_array($files)) {
				$files = [];
			}
		}
		return $files;
	}
	
	public function behaviors()
	{
		return [
			'access' =>	[
				'class' => AccessControl::className(),
				'only' => ['login', 'logout'],
				'rules' => [
					[
						'allow' => true,
						'actions' => [],
						'roles' => ['admins']
					],
				]
			],
			'verbs' => [
				'class' => VerbFilter::className(),
				'actions' => [
					'index' => ['GET'],
					'users' => ['GET'],
					'save' => ['POST']
				]
			]
		];
	}

	
	public function actionIndex()
	{
		return $this->render('index');
	}
	
	//dummy clean
	//TODO: separate and add tests
	public function actionVacuum()
	{
		\Yii::$app->db->open();
		
		$filename = $newSize = $oldSize = $error = null;
		$type = 'UNSUPPORTED TYPE';
		
		$returnMsg = ":type \n :error old size: :oldSize \n new size: :newSize";

		// SQLITE3 VACUUM
		if (\Yii::$app->mycfg->database->format == 'sqlite') {
			$type = 'SQLITE VACUUM';
			$filename = \Yii::$app->mycfg->database->filename;

			try {
				/* @var $pdo \PDO */
				$oldSize = (new \SplFileInfo($filename))->getSize();
				\Yii::$app->db->pdo->query("VACUUM");
				clearstatcache(true, $filename); // we need new size, not old one
				$newSize = (new \SplFileInfo($filename))->getSize();
			} catch (\Exception $e) {
				$error = $e->getMessage();
				$error = "Error: $error \n";
			}
		}
		
		// MYSQL OPTIMIZE
		if (\Yii::$app->mycfg->database->format == 'mysql') {
			$type = 'MYSQL OPTIMIZE';
			try {
				$querySize = <<<SQL
SELECT SQL_NO_CACHE SUM(DATA_LENGTH + INDEX_LENGTH) FROM information_schema.TABLES 
WHERE table_schema = :dbname
GROUP BY table_schema
SQL;
				
				/* @var $sSize \PDOStatement */
				$sSize = \Yii::$app->db->pdo->prepare($querySize);
				$sSize->bindValue(':dbname', \Yii::$app->mycfg->database->dbname);
				$sSize->execute();
				$oldSize = $sSize->fetchColumn();
				$tables = \Yii::$app->db->pdo->query("SHOW TABLES")->fetchAll(\PDO::FETCH_COLUMN);
				$tables = implode(',', $tables);
				\Yii::$app->db->pdo->query("OPTIMIZE TABLE $tables");
				$sSize->execute();
				$newSize = $sSize->fetchColumn();
			} catch (\Exception $e) {
				$error = $e->getMessage();
				$error = "Error: $error \n";
			}
		}
		
		return str_replace([':type',':error',':oldSize',':newSize'], [$type,$error,$oldSize,$newSize], $returnMsg);
	}
	
	// return roles+permissions and users+roles
	public function actionPermissions()
	{
		$data = [];
		$auth = Yii::$app->authManager;
		$roles = $auth->getRoles();
		
		foreach ($roles as $r) {
			$perms = $auth->getChildren($r->name);
			foreach ($perms as $p) {
				$data[$r->name][ $p->name] = $p;
			}
		}

		return $this->renderPartial('permissions', ['roles' => $data, 'perms' => $auth->getPermissions()]);
	}
	
	public function actionCheckFiles()
	{
		// TODO: read with iterator, not all. may use too much memory
		$files_db = [];
		foreach (Books::find()->select(['filename'])->all() as $book) {
			$files_db[] = $book['filename'];
		}
		
		$files = $this->getLibraryBookFilenames();
		$arr_db_only = array_diff($files_db, $files);
		$arr_fs_only = array_diff($files, $files_db);
		
		return json_encode(array(
			'db' => array_values($arr_db_only),
			'fs' => array_values($arr_fs_only)
		), JSON_UNESCAPED_UNICODE);
	}

	public function actionImportFiles()
	{
		if (\Yii::$app->request->getMethod() == 'GET') {
			return json_encode($this->getFiles_FileSystemOnly(), JSON_UNESCAPED_UNICODE);
		}

		if (\Yii::$app->request->getMethod() == 'POST') {
			$error = '';
			$post = \Yii::$app->request->post('post', []);

			$arr_added = [];
			try {
				foreach ($post as $f) {
					$book = new Books(['scenario' => 'import']);
					$book->filename = $book->title = $f;
					$book->insert();
					$arr_added[] = $f;
				}
			}
			catch (\Exception $e) {
				return json_encode(['data' => $arr_added, 'result' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
			}
			
			return json_encode(['data'=> $arr_added, 'result' => true, 'error' => ''], JSON_UNESCAPED_UNICODE);
		}
	}
	
	protected function getFiles_FileSystemOnly()
	{
		// TODO: read with iterator, not all. may use too much memory
		$files_db = [];
		$books = Books::find()->select(['filename'])->asArray()->all();
		//var_dump($books); die;
		foreach ($books as $book) {
			$files_db[] = $book['filename'];
		}
		$files = $this->getLibraryBookFilenames();
		$arr_fs_only = array_values(array_diff($files, $files_db));
		return $arr_fs_only;
	}
	
	
	

	public function actionClearDbFiles()
	{
		//count number of records to clean
		if (\Yii::$app->request->get('count') == 'all') {
			$counter = 0;
			foreach (Books::find()->select(['book_guid','filename'])->each() as $r) {
				$file =
				\Yii::$app->mycfg->Encode(\Yii::$app->mycfg->library->directory.'/'.$r->filename);
				if (!file_exists($file)) {
					$counter++;
				}
			}
			return $counter;
		}
		
		//else clean records in stepping/waves
		$stepping = \Yii::$app->request->get('stepping', 5); //records to delete in 1 wave
		$data = [];
		$counter = 0;
		foreach (Books::find()->select(['book_guid','filename'])->each() as $r) {
			if ($counter >= $stepping) break;
			$file = \Yii::$app->mycfg->Encode(\Yii::$app->mycfg->library->directory.'/'.$r->filename);
			if (!file_exists($file)) {
				Books::deleteAll(['book_guid' => $r->book_guid]);
				$data[] = $r->book_guid;
				$counter++;
			}
		}
		//sleep(1);
		return json_encode($data, JSON_UNESCAPED_UNICODE);
	}
	

	
	public function actionSave()
	{
		$resp = new \stdClass();
		$resp->msg = '';
		$resp->result = false;
		$resp->title = '';
		
		$field = \Yii::$app->request->post('field');
		$value = \Yii::$app->request->post('value');
		 
		list($group, $attr) = explode('_', $field);
		
		try {
			\Yii::$app->mycfg->$group->$attr = $value;
			\Yii::$app->mycfg->save();
			$resp->msg = "<b>$attr</b> was successfully updated";
			$resp->result = true;
		} catch (\Exception $e) {
			$resp->msg = $e->getMessage();
			$resp->result = false;
		} finally {
			$resp->title = $group;
		}

		return json_encode($resp);
	}

}
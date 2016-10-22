<?php
namespace app\controllers; //defaults

use Yii;
use yii\web\Response;
use yii\base\Event;
use yii\helpers\Json;
use yii\helpers\FileHelper;
use yii\data\ArrayDataProvider;
use yii\data\Sort;
use yii\db\ActiveQuery;
use yii\db\Query;
use yii\helpers\Url;
use yii\web\HttpException;


// - - - - -
use \app\components\Controller;
use \app\components\behaviors\EmailSupport;
use \app\models\Books;
use app\models\Users;

class SiteController extends Controller
{	
	public function behaviors()
	{
		return [
			EmailSupport::className(),
			'verb' => [
				'class' => \yii\filters\VerbFilter::class,
				'actions' => [
					'about'	 => ['GET'],
					'index'	 => ['GET'],
					'books'	 => ['GET'],
					'cover'	 => ['GET'],
					'manage' => ['POST']
				]
			],
			'access' =>	[
				'class' => \yii\filters\AccessControl::class,
				'only' => ['login', 'logout', 'books'],
				'rules' => [
					[
						'allow' => true,
						'actions' => ['login', 'books'],
						'roles' => ['?'] // guests
					],
					[
						'allow' => true,
						'actions' => ['logout', 'manage'], //TODO: rule author
						'roles' => ['@'] // users
					],
				]
			]
		];
	}

	
	public function actionLogout()
	{
		/* @var $user \yii\web\User */
		$user = \Yii::$app->user;
		
		$resp = new \stdClass();
		$resp->result = $user->logout();
		
		return json_encode($resp);
	}
	
	
	public function actionLogin()
	{
		/* @var $user \yii\web\User */
		
		//TODO: GET show view, POST login
		//TODO: yii negotiator
		//\Yii::$app->response->format = Response::FORMAT_JSON;
		
		$resp = new \stdClass();
		$resp->result = false;
		
		try {
			$username = \Yii::$app->request->post('username');
			$password = \Yii::$app->request->post('password');
			$remember_me = (bool)\Yii::$app->request->post('remember-me') ? 3600 * 24 * 30 : 0;
			
			$user = \Yii::$app->user;
			$identity = Users::findIdentity($username);
			
			if (empty($identity) || !($identity instanceof \yii\web\IdentityInterface)) {
				throw new \yii\base\InvalidValueException('wrong login or password', 1);
			}
			
			if (!$identity->validatePassword($password)) {
				//var_dump($password);
				throw new \yii\base\InvalidValueException('wrong login or password', 2);
			}
			
			if (!$user->login($identity, $remember_me)) {
				throw new \yii\base\InvalidValueException('server error. please try later', 3);
			}
			
			//success
			$resp->result = true;
		} catch (\Exception $e) {
			//TODO: mitiple errors support
			$resp->data = $e->getMessage();
		}
		
		return json_encode($resp);
	}
	

	public function actionAbout()
	{
		$this->view->title = \Yii::t('frontend/site', 'About');
		$projects = [
			'Yii '.Yii::getVersion() => 'https://github.com/yiisoft/yii2',
			'jQuery' => 'https://jquery.com',
			'jQuery UI' => 'https://jqueryui.com',
			'jQuery Grid' => 'http://www.trirand.com/blog',
			'jQuery Raty' => 'http://wbotelhos.com/raty',
			'jQuery FancyBox' => 'http://fancybox.net'
		];
		return $this->render('//about/index', ['projects' => $projects]);
	}
	
	/* NOT IMPLEMENTED YET
	public function actionError()
	{
	//	yii\helpers\VarDumper::export($var)
		$e = \Yii::$app->errorHandler->exception;
		return $this->render('error', ['exception' => $e]);
	}*/
	
	
	
	public function actionIndex()
	{
		$this->view->title = \Yii::t('frontend/site', 'Books');
		return $this->render('index');
	}

	
	/**
	 * return list of books in jqgrid format 
	 * @return string json
	 */
	public function actionBooks()
	{
		$data = [
			'page' => \Yii::$app->request->get('page'),
			'limit' => \Yii::$app->request->get('rows'),
			'filters' => \Yii::$app->request->get('filters'),
			'sort_column' => \Yii::$app->request->get('sidx'),
			'sort_order'=> \Yii::$app->request->get('sord'), 
		];

		//TODO: currently on store in cookies
		//\Yii::$app->session->set('jqgrid.page', $data['page']);
		
		return Json::encode(Books::jgridBooks($data));
	}
	

	public function actionCover($book_guid)
	{
		return Books::getCover($book_guid);
	}
	
	
	/**
	 * saves cover for book via book_guid. cover is sent as request body
	 */
	public function actionCoverSave()
	{
		/* @var $book Books */
		$book = Books::findOne(['book_guid' => \Yii::$app->request->get('book_guid')]);
		$book->setScenario('cover');
		$book->book_cover = \Yii::$app->request->getRawBody();
		$book->save();
	}
	
	
	/**
	 * add/delete/update functionality for books via jqGrid interface
	 */
	public function actionManage()
	{
		switch (\Yii::$app->request->post('oper')) {
			case 'add':
				$this->add(\Yii::$app->request->post());
				break;
					
			case 'del':
				$this->delete(\Yii::$app->request->post('id'));
				break;
					
			case 'edit':
				$this->update(\Yii::$app->request->post('id'), \Yii::$app->request->post());
				break;
		}
	}
	
	private function add($attributes)
	{
		/* @var $book Books */
		$book = new Books(['scenario'=>'add']);
		$book->attributes = $attributes;
		$book->favorite = $book->favorite == null ? 0 : $book->favorite;
		
		$book->insert();
	}
	
	private function delete($id)
	{
		/* @var $book Books */
		$book = Books::findOne(['book_guid' => $id]);
		
		if ($book instanceof Books) {
			$book->delete();
		}
	}
	
	private function update($id, $attributes)
	{
		/* @var $book Books */
		$book = Books::findOne(['book_guid' => $id]);
		$book->scenario = 'edit';
		$book->attributes = $attributes;
		
		if (!$book->save()) {
			throw new \yii\web\BadRequestHttpException(print_r($book->getErrors(), true));
		}
	}

}
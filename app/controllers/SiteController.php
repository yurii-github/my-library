<?php
namespace app\controllers; //defaults

use Yii;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii\base\Event;
use yii\helpers\Json;
use yii\helpers\FileHelper;
use yii\data\ArrayDataProvider;
use yii\data\Sort;
use yii\db\ActiveQuery;
use yii\db\Query;
use yii\helpers\Url;
use yii\filters\AccessControl;
use yii\web\HttpException;


// - - - - -
use \app\components\Controller;
use \app\components\behaviors\EmailSupport;
use \app\models\Books;

class SiteController extends Controller
{
	function actionTest()
	{
		ob_start();
		
		$m = new \yii\db\Migration();
		//clean
		$m->dropTable('test');
		// 1
		$m->createTable('test', ['a' => 'int', 'b' => 'int']);
		$m->insert('test', ['b' => 'b']); // 0 -- php conversion? why no error?
		// 2
		$m->alterColumn('test', 'b', 'text');
		$m->insert('test', ['b' => 'b']); // 0 -- silent fail, per request s as schema object is never updated
		// 3
		$m->db->schema->getTableSchema('test',true);
		$m->insert('test', ['b' => 'b']); // 'b' - OK
		
		echo str_replace("\n", '<br/>', ob_get_clean());
	}
	
	public function behaviors()
	{
		return [
			EmailSupport::className(),
			'verb' => [
				'class' => VerbFilter::className(),
				'actions' => [
					'about'	 => ['GET'],
					'index'	 => ['GET'],
					'books'	 => ['GET'],
					'cover'	 => ['GET'],
					'manage' => ['POST']
				]
			],
			'access' =>	[
				'class' => AccessControl::className(),
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
	
	
	/**
	 * resamples image to match boundary limits by width. Height is not checked and will resampled according to width's change percentage
	 *
	 * @param string $img_blob image source as blob string
	 * @param int $max_width max allowed width for picture in pixels
	 * @return string image as string BLOB
	 */
	static public function getResampledImageByWidthAsBlob($img_blob, $max_width = 800)
	{
		list($src_w, $src_h) = \getimagesizefromstring($img_blob);

		$src_image = \imagecreatefromstring($img_blob);
		$dst_w = $src_w > $max_width ? $max_width : $src_w;
		$dst_h = $src_w > $max_width ? ($max_width/$src_w*$src_h) : $src_h; //minimize height in percent to width
		$dst_image = \imagecreatetruecolor($dst_w, $dst_h);
		\imagecopyresized($dst_image, $src_image, 0, 0, 0, 0, $dst_w, $dst_h, $src_w, $src_h);
		\ob_start();
		\imagejpeg($dst_image);
		return \ob_get_flush();
	}
	
	
	
	public function actionDbadmin()
	{
		return $this->renderContent(
<<<TXT
<style type="text/css">
iframe#db-admin {
min-height: 100%;
min-width: 100%;
border: 0;
}
</style>
<iframe id="db-admin" src="phpliteAdmin/phpliteAdmin.php"></iframe>
<script type="text/javascript">
function setIframeHeight(iframe) {
    if (iframe) {
        var iframeWin = iframe.contentWindow || iframe.contentDocument.parentWindow;
        if (iframeWin.document.body) {
            iframe.height = iframeWin.document.documentElement.scrollHeight || iframeWin.document.body.scrollHeight;
        }
    }
}
			window.onload = function () {
    setIframeHeight(document.getElementById('db-admin'));
};
</script>
TXT
			);
	}
	
	public function actionLogout()
	{
		\Yii::$app->user->logout();
		$this->redirect(['site/index']);
	}
	
	public function actionLogin()
	{
		try {
			\Yii::$app->response->format = Response::FORMAT_JSON;
			
			$resp = new \stdClass();
			$form = new Login();
			$form->setAttributes([
				'username' => \Yii::$app->request->post('username'),
				'password' => \Yii::$app->request->post('password'),
				'rememberMe' => (bool)\Yii::$app->request->post('remember-me')
			]);
			
			if ($form->login()) {
				$resp->result = true;
				$resp->data = Url::to(['site/index']);
			} else {
				$resp->result = false;
				$resp->data = $form->getErrors();
			}
		} catch(\Exception $e) {
			$resp->result = false;
			$resp->data = ['all' => [$e->getLine().': '.$e->getMessage().' '.$e->getFile()]];
		}
		
		return $resp;
	}
	

	public function actionAbout()
	{
		$this->view->title = \Yii::t('frontend/site', 'About');
		$projects = [
			'Yii 2' => 'https://github.com/yiisoft/yii2',
			'phpliteAdmin' => 'https://code.google.com/p/phpliteadmin',
			'jQuery' => 'https://jquery.com',
			'jQuery UI' => 'https://jqueryui.com',
			'jQuery Grid' => 'http://www.trirand.com/blog',
			'jQuery Raty' => 'http://wbotelhos.com/raty',
			'jQuery FancyBox' => 'http://fancybox.net'
		];
		return $this->render('//about/index', ['projects' => $projects]);
	}
	
	public function actionError()
	{
	//	yii\helpers\VarDumper::export($var)
		$e = \Yii::$app->errorHandler->exception;
		return $this->render('error', ['exception' => $e]);
	}
	
	
	
	public function actionIndex()
	{
		$this->view->title = \Yii::t('frontend/site', 'Books');
		return $this->render('index');
	}

	// ajax
	public function actionBooks()
	{
	//	apcu_clear_cache();
		/* @var $user \yii\web\user */
//		$user = \Yii::$app->user; 
//		if (!$user->can('list-books')) {
//			throw new HttpException(403,'permission denided');
//		}
		//throw new \HttpException(403,'ssssssssss');
		$data = [
			'page' => \Yii::$app->request->get('page'),
			'limit' => \Yii::$app->request->get('rows'),
			'filters' => \Yii::$app->request->get('filters'),
			'sort_column' => \Yii::$app->request->get('sidx'),
			'sort_order'=> \Yii::$app->request->get('sord'), 
		];

		\Yii::$app->session->set('jqgrid.page', $data['page']);
		return Json::encode(Books::jgridBooks($data));
	}
	

	public function actionCover($book_guid)
	{
		return Books::getCover($book_guid);
	}
	
	
	public function actionCoverSave()
	{
		$b = Books::findOne(['book_guid' => \Yii::$app->request->get('book_guid')]);
		$file = file_get_contents("php://input");
		$b->setAttribute('book_cover', self::getResampledImageByWidthAsBlob($file, \Yii::$app->mycfg->book->covermaxwidth));
		$b->save(false,['book_cover']);
	}
	
	
	public function actionManage()
	{
		/* @var $book Books */	
		switch (\Yii::$app->request->post('oper')) {
			case 'add':
				$book = new Books(['scenario'=>'add']);
				$book->attributes = \Yii::$app->request->post();
				$book->favorite = $book->favorite == null ? 0 : $book->favorite;
				$book->insert();
				break;
					
			case 'del':
				$book = Books::findOne(['book_guid' => \Yii::$app->request->post('id')]);
				if ($book instanceof Books) {
					$book->delete();
				}
				break;
					
			case 'edit':
				$book = Books::findOne(['book_guid' => \Yii::$app->request->post('id')]);
				$book->scenario = 'edit';
				$book->attributes = \Yii::$app->request->post();
				$book->save();
				break;
		}
	
		$this->sendEmail(['subject' => 'action: '. @$_POST['oper'],	'data' => 'current action was performed on']);
	}	
	

}
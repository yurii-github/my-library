<?php
namespace app\components;

use yii\base\BootstrapInterface;
use yii\db\Schema;
use yii\base\Event;
use yii\helpers\Url;

final class MyLibraryBootstrap implements BootstrapInterface
{
	/**
	 * (non-PHPdoc)
	 *
	 * @see \yii\base\BootstrapInterface::bootstrap()
	 * @param $app \yii\web\Application        	
	 */
	public function bootstrap($app)
	{


		//echo $app->request->;die;
		//var_dump($app->controller);die;
	//	echo  request->aget('admin'); die;
	//	$app->controllerNamespace = 'backend\\controllers';
		@session_name('session-id');
		/* @var $db \yii\db\Connection */
		/* @var $cfg \frontend\components\Configuration */
		$db = $app->get('db');
		$cfg = \Yii::$app->mycfg;
		date_default_timezone_set($cfg->system->timezone);		
		\Yii::$app->language = $cfg->system->language;

		// inject into app
		//	TODO:  mariadb, postgres, cubrid, oracle, mssql
		try {
			switch ($cfg->database->format) {
				case 'mysql':
					$db->dsn = "mysql:host={$cfg->database->host};dbname={$cfg->database->dbname}";
					$db->username = $cfg->database->login;
					$db->password = $cfg->database->password;
					break;
				case 'sqlite':
					$db->dsn = "sqlite:{$cfg->database->filename}";
					break;
			}
			
			if ($db->getTableSchema('{{%books}}') == null) {  //redirect to migration
				Event::on('app\components\Controller', Controller::EVENT_BEFORE_ACTION, function($e) {
					\Yii::$app->response->redirect(['install/migrate']);
					return false;
				});
			}

		} catch (\Exception $e) {
			$app->session->setFlash('db_init', $e->getMessage());
		}
	}
	
}
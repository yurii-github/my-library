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
		session_name('session-id');
		/* @var $cfg \frontend\components\Configuration */
		$cfg = $app->mycfg;
		$app->setTimeZone($cfg->system->timezone);
		$app->language = $cfg->system->language;

		// inject into app
		//	TODO:  mariadb, postgres, cubrid, oracle, mssql
		try {
			switch ($cfg->database->format) {
				case 'mysql':
					$app->db->dsn = "mysql:host={$cfg->database->host};dbname={$cfg->database->dbname}";
					$app->db->username = $cfg->database->login;
					$app->db->password = $cfg->database->password;
					break;
				case 'sqlite':
					$app->db->dsn = "sqlite:{$cfg->database->filename}";
					break;
			}
			
			
			
			if ($cfg->getVersion() != $cfg->system->version) {  //redirect to migration, as user config doesnot contain matching version
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
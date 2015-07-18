<?php
namespace app\controllers;

use yii\web\Controller;
use yii\web\Application;
use yii\base\Exception;
use yii\db\Command;


/**
 * this class i pure workaround for shit in Yii2
 */
class MigrationController extends \yii\console\controllers\MigrateController
{
	public $stdout;
	
	public function stdout($string)
	{
		$this->stdout .= $string;
	}
	
	function output_callback($buffer, $size = 0)
	{
		$this->stdout($buffer);
	}
}


class InstallController extends Controller
{
	public function actionMigrate()
	{
		$cfg = [
			'db' =>  \Yii::$app->db,
			'migrationTable' => 'yii2_migrations',
			'interactive' => 0,
		];
		$paths = [
			'mylib' => dirname(__DIR__) . '/migrations/',
			'rbac' => \Yii::getAlias('@yii/rbac/migrations')];
		
		/* @var $controllerMigrate MigrationController */
		$controllerMigrateClass = MigrationController::class;
		$controllerMigrate = new $controllerMigrateClass(null, null, $cfg);
		
		ob_start([$controllerMigrate,'output_callback']);
		$controllerMigrate->actionHistory();
		$controllerMigrate->migrationPath = $paths['rbac'];  $controllerMigrate->actionUp();
		$controllerMigrate->migrationPath = $paths['mylib']; $controllerMigrate->actionUp();
		$r = ob_get_clean();
		
		$result = false;
		$content = $controllerMigrate->stdout;
		$content_html = str_replace("\n", '<br/>', $content);
		
		if (stripos('failed', $content) === false) { //successful migration. update config with new version
			$result = true;
			\Yii::$app->mycfg->system->version = \Yii::$app->mycfg->getVersion();
			\Yii::$app->mycfg->save();
		}
		
		//TODO: add success and error messages
		$this->view->title = 'Migration Installer';
		
		return	$this->render('//site/migration', ['result' => $result, 'content' => $content]);
	}

	
}
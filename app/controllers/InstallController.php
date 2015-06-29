<?php
namespace app\controllers;

use yii\web\Controller;
use yii\web\Application;
use yii\base\Exception;
use yii\db\Command;

class InstallController extends Controller
{
	public function actionMigrate()
	{
		$echo = function ($c) { // suppresses echo
			ob_start();
			$c();
			return ob_get_clean();
		};
		
		/* @var $mc \console\controllers\MigrateController */
		define('STDOUT', fopen('php://output', 'w')); //app integration
		$c = require \Yii::getAlias('@app/../console/config/main.php');
		$cfg = $c['controllerMap']['migrate'];
		$cfg['db'] =  \Yii::$app->db;
		$paths = ['mylib' => $cfg['migrationPath'], 'rbac' => \Yii::getAlias('@yii/rbac/migrations')];
		$class = $cfg['class'];
		unset($cfg['class']);
				
		$mc = new $class(null, null, $cfg); //migrate controller
		$data[] = $echo(function() use(&$mc        ) { $mc->actionHistory(); });
		$data[] = $echo(function() use(&$mc, $paths) { $mc->migrationPath = $paths['rbac'];  $mc->actionUp(); });
		$data[] = $echo(function() use(&$mc, $paths) { $mc->migrationPath = $paths['mylib']; $mc->actionUp(); });
		
		// TODO: write version only on success migration
		\Yii::$app->mycfg->system->version = \Yii::$app->mycfg->getVersion();
		\Yii::$app->mycfg->save();
		
		$this->view->title = 'Migration Installer';
		return	$this->render('//site/migration', ['content' => str_replace("\n", '<br/>', implode('<hr/>', $data))]);
	}

	
}
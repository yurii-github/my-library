<?php
namespace modules\apc\controllers;

use yii\web\Controller;

use modules\apc\Module;
use modules\apc\models\APCu;
use yii\web\Response;

class DefaultController extends Controller
{
	public function actionIndex()
	{
		$m = Module::getInstance();
		

		
		return $this->render('index', ['apcu' => (new \modules\apc\models\APCu())]);
	}
	
	
	//google charts
	public function actionCharts()
	{
		\Yii::$app->response->format = Response::FORMAT_JSON;
		
		$apcu = new APCu();
		
		return [
			'usage' => [
				['state', 'size'],
				['Available', ['v' => $apcu->memory_available,'f' => APCu::bsize($apcu->memory_available)] ],
				['Used', ['v' => $apcu->memory_used, 'f' => APCu::bsize($apcu->memory_used)]]
			],
			'hitmiss' => [
				['Element', 'Clicks', ['role'=> 'style']],
				['Hits', $apcu->hits, 'green'],
				['Misses', $apcu->misses, 'red']
			],
			'variables' => [
				APCu::getVariables()
			]
		];
		
	}
}
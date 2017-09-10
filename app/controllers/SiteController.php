<?php
namespace app\controllers;

use app\models\Categories;
use Yii;
use \app\components\Controller;

//TODO: yii negotiator

class SiteController extends Controller
{	
	public function behaviors()
	{
		return [
			'verb' => [
				'class' => \yii\filters\VerbFilter::class,
				'actions' => [
					'about'	 => ['GET'],
					'index'	 => ['GET']
				]
			]
		];
	}


    public function actionIndex()
    {
        $categories = Categories::find()->all();
        $this->view->title = \Yii::t('frontend/site', 'Books');
        return $this->render('index', ['categories' => $categories]);
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

}
<?php
namespace app\controllers;

use app\models\Books;
use yii\data\ActiveDataProvider;
use yii\web\Response;
use yii\web\NotFoundHttpException;

class AdminSiteController extends \yii\web\Controller
{
	public $layout = 'admin-main';
	/**
	 * @param string $id book guid
	 * @throws NotFoundHttpException
	 * @return \common\models\Books
	 */
	protected function getBook($id)
	{
		/* @var $b Books */
		$b = Books::findOne($id);//TODO dont select cover!
		if (empty($b)) {
			\Yii::warning('Wrong book id on '.$this->action->id.': '.$id);
			throw new NotFoundHttpException('The requested page does not exist.');
		}
		
		return $b;
	}
	
	public function actionCover($id)
	{
		return Books::getCover($id);
	}
	
	public function actionIndex()
	{
		throw new \Exception('not fully implemented. stopping..');
		
		$filtered = new Books(['scenario'=>'filter']);
		$filtered->load(\Yii::$app->request->get());
		
		$dp = new ActiveDataProvider([
			'query' => Books::find()->joinWith(['publishers'])
				->andFilterWhere(['like', 'title', $filtered->title])
				->andFilterWhere(['like','publishers.name', $filtered['publishers.name']]),					
			'sort' => [
				'defaultOrder' => ['created_date' => SORT_DESC],
				],
			'pagination' => [
				'pageSize' => 10,
			],
		]);
		$dp->sort->attributes['publishers.name'] = [
			'asc' => ['publishers.name' => SORT_ASC],
			'desc' => ['publishers.name' => SORT_DESC]
		];
		
		return $this->render('list', ['dataProvider' => $dp, 'filterModel' => $filtered]);
	}

	public function actionView()
	{
		$b = $this->getBook(\Yii::$app->request->get('id'));
		return $this->render('view', ['model' => $b]);
	}
	
	
	public function actionCreate()
	{
		$b = new Books(['scenario'=>'add']);
		
		if (\Yii::$app->request->isPost) {//save
			$b->setScenario('edit');
			if ($b->load(\Yii::$app->request->post()) && $b->save()) {
				return $this->redirect(['view', 'id' => $b->book_guid]);
			}
		}
		
		return $this->render('form', ['model' => $b]);
	}

	public function actionDelete($id)
	{
		$this->getBook($id)->delete();
		return $this->redirect(['index']);
	}
	
	public function actionUpdate($id)
	{
		$b = $this->getBook($id);
		if (\Yii::$app->request->isPost) {//save
			$b->setScenario('edit');
			if ($b->load(\Yii::$app->request->post()) && $b->save()) {
				return $this->redirect(['view', 'id' => $b->book_guid]);
			}
		}
		
		return $this->render('form', ['model' => $b]);
	}
	
	
}


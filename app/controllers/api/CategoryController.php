<?php
namespace app\controllers\api;

use app\components\configuration\Book;
use \app\components\Controller;
use app\models\Books;
use app\models\Categories;
use yii\data\ActiveDataProvider;
use yii\web\Response;


class CategoryController extends Controller
{
    public function behaviors()
    {
        return [
            'verb' => [
                'class' => \yii\filters\VerbFilter::class,
                'actions' => [
                    'index'	 => ['GET'],
                    'manage' => ['POST']
                ]
            ]
        ];
    }

    public function actionIndex()
    {

        $book = Books::find()->where(['book_guid' => 'CDDAB3E8-456E-ABE9-CFEE-8203D9519D94'])->one();
        $category = Categories::find()->one();
        //$book->link('categories', $category);
        //$book->save();

        //var_dump($book->categories);die;
        $data = [
            'page' => \Yii::$app->request->get('page'),
            'limit' => \Yii::$app->request->get('rows'),
            'filters' => \Yii::$app->request->get('filters'),
            'sort_column' => \Yii::$app->request->get('sidx'),
            'sort_order'=> \Yii::$app->request->get('sord'),

            'nodeid' => \Yii::$app->request->get('nodeid'), // for marker
        ];

        \Yii::$app->response->format = Response::FORMAT_JSON;
        return Categories::jgridCategories($data);
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
        $item = new Categories();
        $item->attributes = $attributes;
        $item->save();
    }

    private function update($id, $attributes)
    {
        $book_guid = \Yii::$app->request->get('nodeid');

        $category = Categories::findOne(['guid' => $id]);
        $category->attributes = $attributes;

        if (!$category->save()) {
            throw new \yii\web\BadRequestHttpException(print_r($category->getErrors(), true));
        }

        if (!empty($book_guid) && !empty($attributes['marker'])) {
            $book = Books::findOne(['book_guid' => $book_guid]);
            if ($attributes['marker'] == 1) {
                $book->link('categories', $category);
            } else {
                $book->unlink('categories', $category);
            }
        }
    }

    private function delete($id)
    {
        /* @var $category Books */
        $category = Categories::findOne(['guid' => $id]);

        if ($category instanceof Categories) {
            $category->delete();
        }
    }

}

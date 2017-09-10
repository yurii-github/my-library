<?php

namespace app\controllers\api;

use \app\components\Controller;
use app\models\Books;
use yii\web\Response;

class BookController extends Controller
{
    public function behaviors()
    {
        return [
            'verb' => [
                'class' => \yii\filters\VerbFilter::class,
                'actions' => [
                    'index' => ['GET'],
                    'books' => ['GET'],
                    'cover' => ['GET'],
                    'manage' => ['POST']
                ]
            ]
        ];
    }

    public function actionIndex()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        return $this->getBooks();
    }

    /**
     * return list of books in jqgrid format
     * @return string json
     */
    public function getBooks()
    {
        // Example: $x = '%ч'; $y = 'bЧ'; $escape = '\';
        $like = function ($x, $y, $escape) {
            $x = str_replace('%', '', $x);
            $x = preg_quote($x);
            // return false;
            return preg_match('/' . $x . '/iu', $y);
        };

        $db = \Yii::$app->getDb();
        $db->open();
        $db->pdo->sqliteCreateFunction('like', $like);

        $data = [
            'page' => \Yii::$app->request->get('page'),
            'limit' => \Yii::$app->request->get('rows'),
            'filters' => \Yii::$app->request->get('filters'),
            'sort_column' => \Yii::$app->request->get('sidx'),
            'sort_order' => \Yii::$app->request->get('sord'),

            // custom stuff!
            'filterCategories' => \Yii::$app->request->get('filterCategories')
        ];

        //TODO: currently on store in cookies
        return Books::jgridBooks($data);
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
        $book = new Books(['scenario' => 'add']);
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
}

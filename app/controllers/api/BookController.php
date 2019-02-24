<?php
/*
 * My Book Library
 *
 * Copyright (C) 2014-2019 Yurii K.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see http://www.gnu.org/licenses
 */

namespace app\controllers\api;

use \app\components\Controller;
use app\models\Books;
use yii\caching\CacheInterface;
use yii\web\Response;
use \yii\filters\VerbFilter;

class BookController extends Controller
{
    public function behaviors()
    {
        return [
            'verb' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'index' => ['GET'],
                    'books' => ['GET'],
                    'cover' => ['GET'],
                    'manage' => ['POST']
                ]
            ]
        ];
    }

    /**
     * Return list of books in jqgrid format
     *
     * @return \stdClass
     * @throws \yii\db\Exception
     */
    public function actionIndex()
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
        $db->pdo->sqliteCreateFunction('like', $like); // not documented feature!

        $data = [
            'page' => \Yii::$app->request->get('page'),
            'limit' => \Yii::$app->request->get('rows'),
            'filters' => \Yii::$app->request->get('filters'),
            'sort_column' => \Yii::$app->request->get('sidx'),
            'sort_order' => \Yii::$app->request->get('sord'),

            // custom stuff!
            'filterCategories' => \Yii::$app->request->get('filterCategories')
        ];

        \Yii::$app->response->format = Response::FORMAT_JSON;
        return Books::jgridBooks($data);
    }

    /**
     * CRUD functionality for books via jqGrid interface
     *
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
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

    /**
     * @param array $attributes
     * @throws \Throwable
     */
    private function add(array $attributes)
    {
        $book = new Books(['scenario' => 'add']);
        $book->attributes = $attributes;
        $book->favorite = $book->favorite == null ? 0 : $book->favorite;
        $book->insert();
    }

    /**
     * @param string|int $id
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    private function delete($id)
    {
        $book = Books::findOne(['book_guid' => $id]);
        $book->delete();
    }

    private function update($id, $attributes)
    {
        $book = Books::findOne(['book_guid' => $id]);
        $book->scenario = 'edit';
        $book->load($attributes, '');
        $book->save();
    }

    /**
     * @param string $book_guid
     * @return mixed
     */
    public function actionCover($book_guid)
    {
        \Yii::$app->response->headers->set('Cache-Control', 'no-cache');
        \Yii::$app->response->headers->set('Pragma', 'no-cache');
        \Yii::$app->response->headers->set('Content-Type', 'image/jpeg');
        \Yii::$app->response->format = Response::FORMAT_RAW;

        return \Yii::$app->cache->getOrSet(Books::CACHE_BOOK_COVER.$book_guid, function (CacheInterface $cache) use($book_guid) {
            return Books::getCover($book_guid);
        }, 0);
    }

    /**
     * saves cover for book via book_guid. cover is sent as request body
     */
    public function actionCoverSave()
    {
        $book = Books::findOne(['book_guid' => \Yii::$app->request->get('book_guid')]);
        $book->setScenario('cover');
        $book->book_cover = \Yii::$app->request->getRawBody();
        $book->save();
    }
}

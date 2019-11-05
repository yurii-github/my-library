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
use app\models\Categories;
use yii\web\Response;
use \yii\filters\VerbFilter;

class CategoryController extends Controller
{
    public function behaviors()
    {
        return [
            'verb' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'index' => ['GET'],
                    'manage' => ['POST']
                ]
            ]
        ];
    }

    public function actionIndex()
    {
        $data = [
            'page' => \Yii::$app->request->get('page'),
            'limit' => \Yii::$app->request->get('rows'),
            'filters' => \Yii::$app->request->get('filters'),
            'sort_column' => \Yii::$app->request->get('sidx'),
            'sort_order' => \Yii::$app->request->get('sord'),

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
        $request = \Yii::$app->request;

        switch ($request->post('oper')) {
            case 'add':
                $this->add($request->post());
                break;

            case 'del':
                Categories::findOne(['guid' => $request->post('id')])->delete();
                break;

            case 'edit':
                $category = Categories::findOne(['guid' => $request->post('id')]);
                $book = Books::findOne(['book_guid' => $request->get('nodeid')]);

                if ($book) {
                    $this->setMarker($book, $category, (bool)$request->post('marker', false));
                } else {
                    $this->update($category, $request->post());
                }
                break;
        }
    }


    private function add($attributes)
    {
        $category = new Categories();
        $category->load($attributes, '');
        $category->save();
    }


    private function setMarker(Books $book, Categories $category, bool $marker)
    {
        if ($marker) {
            $book->link('categories', $category);
        } else {
            $book->unlink('categories', $category, true);
        }
    }


    private function update(Categories $category, $attributes)
    {
        $category->setAttributes($attributes);
        $category->save();
    }

}

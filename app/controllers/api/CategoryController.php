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
        $category = new Categories();
        $category->load($attributes, '');
        $category->save();
    }

    private function update($id, $attributes)
    {
        $book_guid = \Yii::$app->request->get('nodeid');

        $category = Categories::findOne(['guid' => $id]);
        $category->attributes = $attributes;
        $category->save();

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
        $category = Categories::findOne(['guid' => $id]);
        $category->delete();
    }

}

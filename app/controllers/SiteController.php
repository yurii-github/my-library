<?php
/*
 * My Book Library
 *
 * Copyright (C) 2014-2017 Yurii K.
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

namespace app\controllers;

use app\models\Categories;
use Yii;
use \app\components\Controller;

class SiteController extends Controller
{
    public function behaviors()
    {
        return [
            'verb' => [
                'class' => \yii\filters\VerbFilter::class,
                'actions' => [
                    'about' => ['GET'],
                    'index' => ['GET']
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
            'Yii ' . Yii::getVersion() => 'https://github.com/yiisoft/yii2',
            'jQuery' => 'https://jquery.com',
            'jQuery UI' => 'https://jqueryui.com',
            'jQuery Grid' => 'http://www.trirand.com/blog',
            'jQuery Raty' => 'http://wbotelhos.com/raty',
            'jQuery FancyBox' => 'http://fancybox.net'
        ];
        return $this->render('//about/index', ['projects' => $projects]);
    }

}
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

namespace app\controllers;

use yii\helpers\FileHelper;
use yii\web\Controller;
use \yii\console\controllers\MigrateController as YiiConsoleMigrateController;

class InstallController extends Controller
{
    public $defaultAction = 'migrate';

    public function actionMigrate()
    {
        $this->setupDataDir();

        $cfg = [
            'db' => \Yii::$app->db,
            'migrationTable' => 'yii2_migrations',
            'interactive' => 0,
        ];
        $paths = [
            'mylib' => dirname(__DIR__) . '/migrations/',
            'rbac' => \Yii::getAlias('@yii/rbac/migrations')];

        // this class is pure workaround for shit in Yii2
        $controllerMigrate = new class(null, null, $cfg) extends YiiConsoleMigrateController
        {
            public $stdout;
            public function stdout($string) { $this->stdout .= $string; }
            function output_callback($buffer, $size = 0) { $this->stdout($buffer); }
        };

        ob_start([$controllerMigrate, 'output_callback']);
        $controllerMigrate->actionHistory();
        // NOTE: I cut out any auth stuff as it is useless, uncomment if you want to use authentication
        //$controllerMigrate->migrationPath = $paths['rbac'];
        // $controllerMigrate->actionUp();
        $controllerMigrate->migrationPath = $paths['mylib'];
        $controllerMigrate->actionUp();
        $r = ob_get_clean();

        $result = false;
        $content = $controllerMigrate->stdout;
        $content_html = str_replace("\n", '<br/>', $content);

        if (stripos('failed', $content) === false) { //successful migration. update config with new version
            $result = true;
            \Yii::$app->mycfg->system->version = \Yii::$app->mycfg->getVersion();
            \Yii::$app->mycfg->save();
        }

        //TODO: add success and error messages
        $this->view->title = 'Migration Installer';

        return $this->render('//site/migration', ['result' => $result, 'content' => $content]);
    }

    /**
     * @throws \yii\base\Exception
     */
    protected function setupDataDir(): void
    {
        FileHelper::createDirectory(\Yii::getAlias('@data/books'), 0755, false);
        FileHelper::createDirectory(\Yii::getAlias('@data/logs'), 0755, false);
    }
}

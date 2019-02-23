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

namespace app\components;

use yii\base\BootstrapInterface;
use yii\base\Event;

final class MyLibraryBootstrap implements BootstrapInterface
{
	/**
	 * (non-PHPdoc)
	 *
	 * @see \yii\base\BootstrapInterface::bootstrap()
	 * @param $app \yii\web\Application
	 */
	public function bootstrap($app)
	{
	    $app->view->registerAssetBundle(\app\assets\AppAsset::class);

		/* @var $cfg \app\components\Configuration */
		$cfg = $app->mycfg;
		$app->setTimeZone($cfg->system->timezone);
		$app->language = $cfg->system->language;

		// inject into app
		//	TODO:  mariadb, postgres, cubrid, oracle, mssql
		try {
			switch ($cfg->database->format) {
				case 'mysql':
					$app->db->dsn = "mysql:host={$cfg->database->host};dbname={$cfg->database->dbname}";
					$app->db->username = $cfg->database->login;
					$app->db->password = $cfg->database->password;
					break;
				case 'sqlite':
					$app->db->dsn = "sqlite:{$cfg->database->filename}";
					break;
			}

			if ($cfg->getVersion() != $cfg->system->version) {  //redirect to migration, as user config doesnot contain matching version
				Event::on('app\components\Controller', Controller::EVENT_BEFORE_ACTION, function($e) {
					\Yii::$app->response->redirect(['install/migrate']);
					return false;
				});
			}

		} catch (\Exception $e) {
			$app->session->setFlash('db_init', $e->getMessage());
		}
	}

}

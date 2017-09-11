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

namespace app\assets;

class JqueryUI extends AssetBundle
{
    public $sourcePath = null;
    public $depends = [\yii\web\JqueryAsset::class];

    public function init()
    {
        $theme = \Yii::$app->mycfg->system->theme;
        $this->js = ["https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"];
        $this->css = ["https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/$theme/jquery-ui.css"];

        parent::init();
    }
}
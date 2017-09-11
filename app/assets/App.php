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

class App extends AssetBundle
{
    public $publishOptions = ['const-dir' => 'app'];
    public $css = ['app/css/yui-reset-3.5.0.css', 'app/css/style.css'];

    public $depends = [
        \yii\web\JqueryAsset::class,

        //GII/YII BUG: asset override is not loaded in Gii, so we force it to load
        \yii\bootstrap\BootstrapAsset::class,
        \yii\bootstrap\BootstrapPluginAsset::class,
        //\yii\gii\TypeAheadAsset::class,

        JqueryUI::class,
        JqueryRaty::class,
        JqueryFancybox::class,
        JqueryGrid::class
    ];

    public $js = ['js.cookie.js'];
}
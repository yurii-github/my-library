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

class JqueryGrid extends AssetBundle
{
    public $sourcePath = null;
    public $depends = [\yii\web\JqueryAsset::class, 'app\assets\JqueryUI'];

    public $css = ['https://cdnjs.cloudflare.com/ajax/libs/jqgrid/4.6.0/css/ui.jqgrid.css'];
    public $js = ['https://cdnjs.cloudflare.com/ajax/libs/jqgrid/4.6.0/js/jquery.jqGrid.min.js'];

    public function init()
    {
        $supported = ['uk-UA' => 'ua'];

        if (array_key_exists(\Yii::$app->language, $supported)) {
            $this->js[] = "https://cdnjs.cloudflare.com/ajax/libs/jqgrid/4.6.0/js/i18n/grid.locale-{$supported[\Yii::$app->language]}.js";
        } else {
            $this->js[] = 'https://cdnjs.cloudflare.com/ajax/libs/jqgrid/4.6.0/js/i18n/grid.locale-en.js';
        }

        parent::init();
    }
}
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

namespace app\assets;

class AppAsset extends AssetBundle
{
    public $js = ['js.cookie.js'];

    public $depends = [
        FontAwesomeIcons::class,
        JqueryAsset::class,
        JqueryUI::class,
        JqueryFancybox::class,
        JqueryRaty::class,
        JqueryGrid::class,
        StyleAsset::class
    ];
}

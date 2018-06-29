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

class JqueryRaty extends AssetBundle
{
    public $js = [
        [
            'https://cdnjs.cloudflare.com/ajax/libs/raty/2.8.0/jquery.raty.min.js',
            'integrity' => 'sha256-S3dyvT3x/xsSYiVFiz7Qrwbq1FYHL3MivpzraOAy9x8=',
            'crossorigin' => 'anonymous'
        ],
    ];

    public $css = [
        [
            'https://cdnjs.cloudflare.com/ajax/libs/raty/2.8.0/jquery.raty.min.css',
            'integrity' => 'sha256-LrmQrZI7viiZxHhADTAK//E2pEXLLiBer63y/S0Aniw=',
            'crossorigin' => 'anonymous'
        ]
    ];

    public $depends = [JqueryAsset::class];
}
<?php
/*
 * My Book Library
 *
 * Copyright (C) 2014-2021 Yurii K.
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

namespace App;

use \SplFileInfo;

class PhpCliServer
{
    public static function isCliServer()
    {
        return PHP_SAPI === 'cli-server';
    }
    
    public static function handle(): bool 
    {
        if (!self::isCliServer()) {
            return false;
        }

        $url = parse_url($_SERVER['REQUEST_URI']);
        $filename = WEB_DIR . $url['path'];

        if (!preg_match('/\.(?:png|js|jpg|jpeg|gif|css|ico)$/', $filename)) {
            return false;
        }

        if (!file_exists($filename)) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
            printf('"%s" does not exist', $_SERVER['REQUEST_URI']);
            return true;
        }

        $fi = new SplFileInfo($filename);
        if ($fi->getExtension() === 'css') {
            $mime = 'text/css';
        } else {
            $mime = mime_content_type($filename);
        }
        header('Content-Type: ' . $mime);
        readfile($filename);
        return true;
    }
}
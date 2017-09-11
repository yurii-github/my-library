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

namespace app\components;

class ApcCache extends \yii\caching\ApcCache
{

    public function init()
    {
        $this->useApcu = true;
        parent::init();
    }

    /**
     * (non-PHPdoc)
     * @see \yii\caching\Cache::buildKey()
     * @param $key array|string if string use as is, if array, implode with md5 hashing
     */
    public function buildKey($key)
    {
        if (is_array($key)) {
            $key = $key[0] . ':' . md5(implode('', $key));
        }
        return $this->keyPrefix . $key;
    }

}
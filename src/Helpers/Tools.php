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

namespace App\Helpers;

use App\Exception\InvalidImageException;

class Tools
{
    /**
     * resamples image to match boundary limits by width. Height is not checked and will resampled according to width's change percentage
     *
     * @param string $img_blob image source as blob string
     * @param int $max_width max allowed width for picture in pixels
     *
     * @return string image as string BLOB
     */
    /**
     * @param $img_blob
     * @param int $max_width
     * @throws InvalidImageException
     * @return false|string
     */
    static public function getResampledImageByWidthAsBlob($img_blob, $max_width = 800)
    {
        $size = getimagesizefromstring($img_blob);
        
        if ($size === false) {
            throw new InvalidImageException();
        }
        
        list($src_w, $src_h) = getimagesizefromstring($img_blob);

        $src_image = imagecreatefromstring($img_blob);
        $dst_w = $src_w > $max_width ? $max_width : $src_w;
        $dst_h = $src_w > $max_width ? ($max_width / $src_w * $src_h) : $src_h; //minimize height in percent to width
        $dst_image = imagecreatetruecolor($dst_w, $dst_h);
        imagecopyresized($dst_image, $src_image, 0, 0, 0, 0, $dst_w, $dst_h, $src_w, $src_h);
        ob_start();
        imagejpeg($dst_image);

        return ob_get_clean();
    }

    /**
     * generates global unique id
     *
     * format: hhhhhhhh-hhhh-hhhh-hhhh-hhhhhhhhhhhh
     *
     * @return string GUID
     */
    static public function com_create_guid()
    {
        mt_srand((double)microtime() * 10000);
        $charid = strtoupper(md5(uniqid(rand(), true)));
        return substr($charid, 0, 8) . '-' . substr($charid, 8, 4) . '-' . substr($charid, 12, 4) . '-' . substr($charid, 16, 4) . '-' . substr($charid, 20, 12);
    }
}

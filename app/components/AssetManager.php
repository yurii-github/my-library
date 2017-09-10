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

use yii\helpers\FileHelper;


class AssetManager extends \yii\web\AssetManager
{
    /**
     * {@inheritdoc}
     *
     * EXTRA:
     * @see \yii\web\AssetManager::publishDirectory()
     *
     *  [const-dir] contain directory name or empty if copy current asset directly to base assets' dir
     */
    public function publishDirectory($src, $options)
    {
        // default behavior with hashed dir
        if (!isset($options['const-dir'])) {
            return parent::publishDirectory($src, $options);
        }

        //
        // my custom : don't generate random dir, instead, use custom if set
        //
        $dstDir = $this->basePath . (!empty($options['const-dir']) ? '/' . $options['const-dir'] : '');
        //dont copy if already was copied
        // TODO: add datetime checks
        if (file_exists($dstDir)) {
            return [$dstDir, $this->baseUrl];
        }
        // A. copy only subdirs if set
        if (!empty($options['sub-dirs']) && is_array($options['sub-dirs'])) {
            foreach ($options['sub-dirs'] as $subdir) {
                if (is_dir($src . '/' . $subdir)) {
                    FileHelper::copyDirectory($src . '/' . $subdir, $dstDir . '/' . $subdir, [
                        'dirMode' => $this->dirMode,
                        'fileMode' => $this->fileMode,
                        'beforeCopy' => @$options['beforeCopy'],
                        'afterCopy' => @$options['afterCopy'],
                        'forceCopy' => @$options['forceCopy']
                    ]);
                } //TODO: else write error log
            }
        } else { //copy whole dir
            FileHelper::copyDirectory($src, $dstDir, [
                'dirMode' => $this->dirMode,
                'fileMode' => $this->fileMode,
                'beforeCopy' => @$options['beforeCopy'],
                'afterCopy' => @$options['afterCopy'],
                'forceCopy' => @$options['forceCopy']
            ]);
        }

        return [$dstDir, $this->baseUrl];
    }


    protected function publishFile($src)
    {
        //TODO: check custom behavior
        return parent::publishFile($src);
    }
}
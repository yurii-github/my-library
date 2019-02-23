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

namespace app\components\log;

use yii\helpers\VarDumper;

class FileTarget extends \yii\log\FileTarget
{
    public $with_microtime = false;

    /**
     * @inheritdoc
     */
    public function formatMessage($message)
    {
        if (!$this->with_microtime) {
            return parent::formatMessage($message);
        }

        list($text, $level, $category, $timestamp) = $message;
        $level = \yii\log\Logger::getLevelName($level);
        if (!is_string($text)) {
            $text = VarDumper::export($text);
        }
        $traces = [];
        if (isset($message[4])) {
            foreach ($message[4] as $trace) {
                $traces[] = "in {$trace['file']}:{$trace['line']}";
            }
        }

        $prefix = $this->getMessagePrefix($message);

        return
            (new \DateTime())->setTimestamp($timestamp)->format('Y-m-d H:i:s') . ':' . str_pad(round(($timestamp - floor($timestamp)) * 1000000), 6, '0', STR_PAD_RIGHT)
            . " {$prefix}[$level][$category] $text"
            . (empty($traces) ? '' : "\n    " . implode("\n    ", $traces));
    }

}

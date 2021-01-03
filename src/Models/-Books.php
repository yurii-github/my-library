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

namespace app\models;

use app\components\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\web\HttpException;
use app\Helpers\Tools;


/**
 * Book entity AR
 *
 * @property string $book_guid
 * @property string $created_date
 * @property string $updated_date
 * @property string $book_cover binary cover or yii\web\UploadedFile before save!
 * @property float $favorite
 * @property string $read 'yes'|'no'
 * @property int $year
 * @property string $title
 * @property string $isbn13
 * @property string $author
 * @property string $publisher
 * @property string $ext
 * @property string $filename
 * @property-read Categories[] $categories
 */
class Books extends ActiveRecord
{
    /** @var string 'book.cover.{book_guid}' book cover */
    const CACHE_BOOK_COVER = 'book.cover.';

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            // - cover (get)
            [['book_cover'], 'string', 'on' => ['cover']],
            
            
            // - filter (get)
            [['title', 'publishers.name'], 'string', 'on' => ['filter'] /*  'message' => 'must be integer!'*/],
            // - import (from fs, get)
            [['title', 'filename'], 'safe', 'on' => 'import'],
            // add (insert)
            [['created_date', 'updated_date', 'book_guid', 'favorite', 'read', 'year', 'title', 'isbn13', 'author', 'publisher', 'ext', 'filename'], 'safe', 'on' => 'add']
        ];
    }



}

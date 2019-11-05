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
use app\helpers\Tools;


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
            // - edit (update)
            [['year'], 'integer', 'on' => ['edit']],
            [['favorite'], 'number', 'on' => ['edit']],
            [['updated_date', 'favorite', 'read', 'year', 'title', 'isbn13', 'author', 'publisher', 'ext'], 'safe', 'on' => 'edit'],
            ['book_cover', 'image', 'skipOnEmpty' => true, 'extensions' => 'gif,jpg,png', 'on' => ['edit']],
            // - filter (get)
            [['title', 'publishers.name'], 'string', 'on' => ['filter'] /*  'message' => 'must be integer!'*/],
            // - import (from fs, get)
            [['title', 'filename'], 'safe', 'on' => 'import'],
            // add (insert)
            [['created_date', 'updated_date', 'book_guid', 'favorite', 'read', 'year', 'title', 'isbn13', 'author', 'publisher', 'ext', 'filename'], 'safe', 'on' => 'add']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function find()
    {
        return parent::find()->select([
            'book_guid',
            // 'book_cover', <-- ignore cover on regular select for performance gains!
            'created_date',
            'updated_date',
            'favorite',
            'read',
            'year',
            'title',
            'isbn13',
            'author',
            'publisher',
            'ext',
            'filename'
        ]);
    }

    /**
     * @return ActiveQuery
     * @throws \yii\base\InvalidConfigException
     */
    public function getCategories()
    {
        return $this->hasMany(Categories::class, ['guid' => 'category_guid'])
            ->viaTable('books_categories', ['book_guid' => 'book_guid']);
    }

    /**
     * resamples image to match boundary limits by width. Height is not checked and will resampled according to width's change percentage
     *
     * @param string $img_blob image source as blob string
     * @param int $max_width max allowed width for picture in pixels
     *
     * @return string image as string BLOB
     */
    static public function getResampledImageByWidthAsBlob($img_blob, $max_width = 800)
    {
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

    public function buildFilename()
    {
        return str_replace(array(
            '{year}',
            '{title}',
            '{publisher}',
            '{author}',
            '{isbn13}',
            '{ext}'
        ), array(
            $this->year,
            $this->title,
            $this->publisher,
            $this->author,
            $this->isbn13,
            $this->ext
        ), \Yii::$app->mycfg->book->nameformat);
    }

    public function behaviors()
    {
        return [
            'autotime' => [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_date',
                'updatedAtAttribute' => 'updated_date',
                'value' => function () {
                    return (new \DateTime())->format('Y-m-d H:i:s');
                }
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function afterDelete()
    {
        $this->flushCache();

        $filename_utf8 = \Yii::$app->mycfg->library->directory . $this->filename;
        $filename_encoded = \Yii::$app->mycfg->Encode($filename_utf8);

        if (\Yii::$app->mycfg->library->sync) {
            if (!file_exists($filename_encoded)) {
                \Yii::warning("file '{$filename_utf8}' was removed before record deletion with sync enabled");
            } else {
                unlink($filename_encoded);
            }
        }

        parent::afterDelete();
    }

    private function myBeforeInsert()
    {
        $this->book_guid = Tools::com_create_guid();

        if ($this->getScenario() != 'import') {
            $this->filename = $this->buildFilename();
        }

        return true;
    }

    /**
     * + update filename in database and rename filename in filesystem accordinly
     * + resize and update book cover
     *
     * @throws \yii\base\InvalidValueException
     * @throws HttpException
     * @return boolean
     */
    private function myBeforeUpdate()
    {
        if ($this->book_cover) {//resize
            $this->book_cover = self::getResampledImageByWidthAsBlob($this->book_cover, \Yii::$app->mycfg->book->covermaxwidth);
        }

        // just cover update, ignore anything else
        if ($this->getScenario() == 'cover') {
            return true;
        }

        // sync with filesystem is enabled. update filename and rename physical file
        if (\Yii::$app->mycfg->library->sync && $this->filenameAttrsChanged()) {
            $old_filename = $this->getOldAttribute('filename');
            $new_filename = $this->buildFilename();
            $this->filename = $new_filename; // will be stored in database
            $filename_encoded_old = \Yii::$app->mycfg->Encode(\Yii::$app->mycfg->library->directory . $old_filename);
            $filename_encoded_new = \Yii::$app->mycfg->Encode(\Yii::$app->mycfg->library->directory . $new_filename);

            // update file in filesystem
            if ($filename_encoded_old != $filename_encoded_new) {
                if (!file_exists($filename_encoded_old)) {
                    throw new \yii\base\InvalidValueException("Sync for file failed. Source file '{$filename_encoded_old}' does not exist", 1);
                }
                // PHP 7: throw error if file is open
                if (!rename($filename_encoded_old, $filename_encoded_new)) {
                    throw new HttpException(500, "Failed to rename file. \n\n OLD: $filename_encoded_old \n\n NEW: $filename_encoded_new ");
                }
            }
        }

        return true;
    }

    /**
     * checks if filename dependant attributes were changed
     * @return bool
     */
    private function filenameAttrsChanged()
    {
        $isChanged = false;
        $keys = ['year', 'title', 'isbn13', 'author', 'publisher', 'ext'];

        foreach ($keys as $key) {
            if ($this->getOldAttribute($key) != $this->$key) {
                $isChanged = true;
                break;
            }
        }

        return $isChanged;
    }

    protected function flushCache()
    {
        if (\Yii::$app->cache) {
            \Yii::$app->cache->delete(static::CACHE_BOOK_COVER. $this->book_guid);
        }
    }

    /**
     * @param bool $insert
     * @return bool
     * @throws HttpException
     */
    public function beforeSave($insert)
    {
        $this->flushCache();

        // yii2 event handling logic. do not remove!
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if ($insert) { // INSERT
            return $this->myBeforeInsert();
        } else { // UPDATE
            return $this->myBeforeUpdate();
        }
    }

    /**
     * gets records in jqGrid format
     *
     * @param array $data
     * @return \stdClass
     */
    public static function jgridBooks(array $data)
    {
        $nameColumns = ['created_date', 'book_guid', 'favorite', 'read', 'year', 'title', 'isbn13', 'author', 'publisher', 'ext', 'filename'];
        $sortColumns = ['favorite', 'read', 'year', 'title', 'created_date', 'isbn13', 'author', 'publisher'];

        $query = self::find()->alias('b')->select(['b.created_date', 'b.book_guid', 'b.favorite', 'b.read', 'b.year', 'b.title', 'b.isbn13', 'author', 'publisher', 'ext', 'filename']);

        if (!empty($data['filterCategories'])) {
            $query->innerJoinWith(['categories' => function (ActiveQuery $q) use ($data) {
                $q->where(['in', 'guid', explode(',', $data['filterCategories'])]);
            }]);
        }

        return self::jgridRecords($data, $nameColumns, $sortColumns, $query);
    }


    /**
     * @return string binary book cover
     */
    public static function getCover($guid)
    {
        $cover = self::find()->select(['book_cover'])->where(['book_guid' => $guid])->limit(1)->scalar();
        return $cover ? $cover : file_get_contents(\Yii::getAlias('@webroot') . '/assets/app/book-cover-empty.jpg');
    }

}

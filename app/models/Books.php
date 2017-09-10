<?php

namespace app\models;

use app\components\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\web\Response;
use yii\web\HttpException;
use app\helpers\Tools;


/**
 * @property string $created_date
 * @property string $updated_date
 * @property string $book_guid
 * @property string $filename
 * @property string $book_cover binary cover or yii\web\UploadedFile before save!
 */
class Books extends ActiveRecord
{

    /**
     * (non-PHPdoc)
     * @see \yii\base\Model::rules()
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

    public function getCategories()
    {
        return $this->hasMany(Categories::className(), ['guid' => 'category_guid'])
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
        // TODO: filesystem security
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
                    //	return \Yii::$app->formatter->asDatetime('now','php:Y-m-d H:i:s'); BUGGED!!! doesnt change timezone on Travis. no clue why
                    return (new \DateTime())->format('Y-m-d H:i:s');
                }
            ]
        ];
    }

    /**
     * (non-PHPdoc)
     * @see \yii\db\BaseActiveRecord::afterDelete()
     */
    public function afterDelete()
    {
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

    public function beforeSave($insert)
    {
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

    public function attributeLabels()
    {
        return [
            'title' => 'title',
            'created_date' => 'created',
            'updated_date' => 'updated',
            'publishers.name' => 'publisher'

        ];
    }

    public static function getCover($id)
    {
        header("Cache-Control: no-cache");
        header("Pragma: no-cache");
        header('Content-Type: image/jpeg');
        \Yii::$app->response->format = Response::FORMAT_RAW;
        $cache_name = 'book-cover-' . (empty($id) ? 'empty' : $id);

        if (\Yii::$app->cache->exists($cache_name)) {
            return \Yii::$app->cache->get('book-cover-' . $id);
        }

        $book = self::find()->select(['book_cover'])->where('book_guid = :book_guid', ['book_guid' => $id])->asArray()->one();

        if (empty($book['book_cover'])) {
            $book['book_cover'] = file_get_contents(\Yii::getAlias('@webroot') . '/assets/app/book-cover-empty.jpg');
            \Yii::$app->cache->set('book-cover-empty', $book['book_cover']); //don't cache empty
        } else {
            \Yii::$app->cache->set($cache_name, $book['book_cover'], 3600);
        }

        return $book['book_cover'];
    }

}
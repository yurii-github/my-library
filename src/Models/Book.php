<?php

namespace App\Models;

use App\Configuration\Configuration;
use App\Helpers\Tools;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $book_guid
 * @property string $created_date
 * @property string $updated_date
 * @property string $book_cover binary cover
 * @property float $favorite
 * @property string $read 'yes'|'no'
 * @property int $year
 * @property string $title
 * @property string $isbn13
 * @property string $author
 * @property string $publisher
 * @property string $ext
 * @property string $filename
 * @property-read Category[] $categories
 *
 * @mixin Builder
 */
class Book extends Model
{
    const CREATED_AT = 'created_date'; // TODO: return (new \DateTime())->format('Y-m-d H:i:s');
    const UPDATED_AT = 'updated_date';

    public $incrementing = false;

    protected $table = 'books';
    protected $primaryKey = 'book_guid';
    protected $keyType = 'string';
    protected $fillable = [
        'year',
        'favorite',
        'read',
        'title',
        'isbn13',
        'author',
        'publisher',
        'ext',
    ];


    protected static function boot()
    {
        /** @var Configuration $config */
        $config = Container::getInstance()->get(Configuration::class);

        parent::boot();

        static::deleted(function (self $book) use ($config) {
            $filename = $config->getFilepath($book->filename);
            if ($config->library->sync) {
                if (!file_exists($filename)) {
                    throw new \Exception("file '{$filename}' was removed before record deletion with sync enabled");
                } else {
                    unlink($filename);
                }
            }
        });

        static::creating(function (self $book) {
            $book->book_guid = Tools::com_create_guid();
            $book->favorite = $book->favorite == null ? 0 : $book->favorite;
        });

        /*
         * update filename in database and rename filename in filesystem accordinly
         */
        static::updating(function (self $book) use ($config) {
            // sync with filesystem is enabled. update filename and rename physical file
            if ($config->library->sync && self::filenameAttrsChanged($book)) {
                $old_filename = $book->getOriginal('filename');
                $new_filename = $this->buildFilename();
                $book->filename = $new_filename;
                $filepathOld = $config->getFilepath($old_filename);
                $filepathNew = $config->getFilepath($new_filename);

                // update file in filesystem
                if ($filepathOld != $filepathNew) {
                    if (!file_exists($filepathOld)) {
                        throw new \InvalidArgumentException("Sync for file failed. Source file '{$filepathOld}' does not exist", 1);
                    }
                    // PHP 7: throw error if file is open
                    if (!rename($filepathOld, $filepathNew)) {
                        throw new \Exception("Failed to rename file. \n\n OLD: $filepathOld \n\n NEW: $filepathNew ");
                    }
                }
            }

            return true;
        });
    }

    /**
     * checks if filename dependant attributes were changed
     *
     * @param Book $book
     * @return bool
     */
    protected static function filenameAttrsChanged(self $book)
    {
        $isChanged = false;
        $keys = ['year', 'title', 'isbn13', 'author', 'publisher', 'ext'];

        foreach ($keys as $key) {
            if ($book->getOriginal($key) !== $book->$key) {
                $isChanged = true;
                break;
            }
        }

        return $isChanged;
    }


    public static function buildFilename(self $book, $format)
    {
        return str_replace(array(
            '{year}',
            '{title}',
            '{publisher}',
            '{author}',
            '{isbn13}',
            '{ext}'
        ), array(
            $book->year,
            $book->title,
            $book->publisher,
            $book->author,
            $book->isbn13,
            $book->ext
        ), $format);
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'books_categories', 'book_guid', 'category_guid', 'book_guid', 'guid');
    }
}
<?php

namespace App\Models;

use App\Configuration\Configuration;
use App\Exception\BookFileException;
use App\Exception\BookFileNotFoundException;
use App\Helpers\Tools;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * @property string $book_guid
 * @property Carbon $created_date
 * @property Carbon $updated_date
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
 * @property-read Collection|Category[] $categories
 * @property-read bool $file_exists
 *
 * @mixin Builder
 */
class Book extends Model
{
    const CREATED_AT = 'created_date';
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
    protected $casts = [
        'favorite' => 'float'
    ];


    protected static function boot()
    {
        parent::boot();

        $config = Container::getInstance()->get(Configuration::class);
        assert($config instanceof Configuration);

        static::deleted(function (self $book) use ($config) {
            if ($config->library->sync) {
                $filepath = $config->getFilepath($book->filename);
                // TODO: write warning into log // "File '{$filepath}' was removed before record deletion with sync enabled"
                if (file_exists($filepath)) {
                    unlink($filepath);
                }
            }
        });

        static::creating(function (self $book) use ($config) {
            $book->book_guid = Tools::com_create_guid();
            $book->favorite = $book->favorite == null ? 0 : $book->favorite;
            if (empty($book->filename)) {
                $book->filename = self::buildFilename($book, $config->book->nameformat);
            }
            if ($config->library->sync) {
                $filepath = $config->getFilepath($book->filename);
                if (!file_exists($filepath)) {
                    throw new BookFileNotFoundException("Book '{$filepath}' does not exist.", 1);
                }
            }
        });

        /*
         * update filename in database and rename filename in filesystem accordingly
         */
        static::updating(function (self $book) use ($config) {
            if (self::filenameAttrsChanged($book)) {
                $oldFilename = $book->getOriginal('filename');
                $book->filename = self::buildFilename($book, $config->book->nameformat);
                // sync with filesystem is enabled. update filename and rename physical file
                if ($config->library->sync) {
                    $filepathOld = $config->getFilepath($oldFilename);
                    $filepathNew = $config->getFilepath($book->filename);
                    // update file in filesystem
                    if ($filepathOld != $filepathNew) {
                        if (!file_exists($filepathOld)) {
                            throw new BookFileNotFoundException("Sync for file failed. Source file '{$filepathOld}' does not exist", 2);
                        }
                        if (!@rename($filepathOld, $filepathNew)) {
                            $err = error_get_last()['message'] ?? '';
                            throw new BookFileException("Failed to rename file '{$filepathOld} to {$filepathNew}. {$err}");
                        }
                    }
                }
            }
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

    public function getFileExistsAttribute(): bool
    {
        $config = Container::getInstance()->get(Configuration::class);
        assert($config instanceof Configuration);

        return file_exists($config->getFilepath($this->filename));
    }
    
    public function getFilepath(): string 
    {
        $config = Container::getInstance()->get(Configuration::class);
        assert($config instanceof Configuration);

        return $config->getFilepath($this->filename);
    }
}
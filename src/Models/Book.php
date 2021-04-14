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

namespace App\Models;

use App\Configuration\Configuration;
use App\Exception\BookFileException;
use App\Exception\BookFileNotFoundException;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Ramsey\Uuid\Uuid;

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
 *
 * @property-read Collection|Category[] $categories
 * @property BookFile $file
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
    ];
    protected $casts = [
        'favorite' => 'float'
    ];

    protected ?BookFile $attrFile = null; // set 'null' is a fix for ORM


    /**
     * @inheritDoc
     */
    protected static function boot()
    {
        parent::boot();

        $config = Container::getInstance()->get(Configuration::class);
        assert($config instanceof Configuration);

        static::deleted(function (self $book) use ($config) {
            if ($config->library->sync && $book->file) {
                $book->file->delete();
            }
        });

        static::creating(function (self $book) use ($config) {
            $book->book_guid = strtoupper(Uuid::uuid4());
            $book->favorite = $book->favorite == null ? 0 : $book->favorite;
            if (!$book->file) {
                self::changeFileFormat($book, $config->book->nameformat);
            }
            if ($config->library->sync) {
                if (!$book->file->exists()) {
                    throw new BookFileNotFoundException("Book '{$book->file->getFilepath()}' does not exist.");
                }
            }
        });

        /*
         * update filename in database and rename filename in filesystem accordingly
         */
        static::updating(function (self $book) use ($config) {
            if (self::filenameAttrsChanged($book)) {
                $oldFilename = $book->getOriginal('filename');
                self::changeFileFormat($book, $config->book->nameformat);
                // sync with filesystem is enabled. update filename and rename physical file
                if ($config->library->sync) {
                    $filepathOld = $config->getFilepath($oldFilename);
                    $filepathNew = $book->file->getFilepath();
                    // update file in filesystem
                    if ($filepathOld !== $filepathNew) {
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
    protected static function filenameAttrsChanged(self $book): bool
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

    /**
     * @return BelongsToMany
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'books_categories', 'book_guid', 'category_guid', 'book_guid', 'guid');
    }


    public function setFileAttribute(BookFile $file)
    {
        $this->attrFile = $file;
        $this->setAttribute('filename', $file->getFilename());
        return $this;
    }

    public function getFileAttribute(): ?BookFile
    {
        if (!$this->attrFile && $filename = $this->getAttribute('filename')) {
            $this->attrFile = new BookFile($this->getAttribute('filename'));
        }

        return $this->attrFile;
    }


    protected static function changeFileFormat(Book $book, $format): void
    {
        $oldFilename = $book->getAttribute('filename');
        $newFilename = str_replace(array(
            '{year}',
            '{title}',
            '{publisher}',
            '{author}',
            '{isbn13}',
        ), array(
            $book->year,
            $book->title,
            $book->publisher,
            $book->author,
            $book->isbn13,
        ), $format);
        $newFilename .= '.' . ($oldFilename ? (new BookFile($oldFilename))->getExtension() : '');

        $book->file = new BookFile($newFilename);
    }
}
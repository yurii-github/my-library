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
    
    protected $table='books';
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
        parent::boot();
        
        static::deleted(function(self $book) {
            /** @var Configuration $config */
            $config = Container::getInstance()->get(Configuration::class);
            
            $filename = $config->library->directory . $book->filename; 

            if ($config->library->sync) {
                if (!file_exists($filename)) {
                    throw new \Exception("file '{$filename}' was removed before record deletion with sync enabled");
                } else {
                    throw new \Exception('UNLINK '. $filename);
                    //unlink($filename);
                }
            }
        });
        
        static::creating(function(self $book) {
            $book->book_guid = Tools::com_create_guid();
            $book->favorite = $book->favorite == null ? 0 : $book->favorite;
        });
        
        /*
         * + update filename in database and rename filename in filesystem accordinly
         * + resize and update book cover
         */
        static::updating(function(self $book) {
            /** @var Configuration $config */
            $config = Container::getInstance()->get(Configuration::class);

            // sync with filesystem is enabled. update filename and rename physical file
            if ($config->library->sync && $this->filenameAttrsChanged($book)) {
                $old_filename = $book->getOriginal('filename');
                $new_filename = $this->buildFilename();
                $book->filename = $new_filename;
                $filename_encoded_old = $config->library->directory . $old_filename;
                $filename_encoded_new = $config->library->directory . $new_filename;

                // update file in filesystem
                if ($filename_encoded_old != $filename_encoded_new) {
                    if (!file_exists($filename_encoded_old)) {
                        throw new \InvalidArgumentException("Sync for file failed. Source file '{$filename_encoded_old}' does not exist", 1);
                    }
                    // PHP 7: throw error if file is open
                    if (!rename($filename_encoded_old, $filename_encoded_new)) {
                        throw new \Exception("Failed to rename file. \n\n OLD: $filename_encoded_old \n\n NEW: $filename_encoded_new ");
                    }
                }
            }

            return true;
        });
    }

    /**
     * checks if filename dependant attributes were changed
     * @return bool
     */
    protected function filenameAttrsChanged(self $book)
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
    

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'books_categories', 'book_guid', 'category_guid', 'book_guid', 'guid');
    }
}
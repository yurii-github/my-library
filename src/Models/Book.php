<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
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
 * @property-read Category[] $categories
 * 
 * @mixin Builder
 */
class Book extends Model
{
    public $incrementing = false;
    protected $table='books';
    protected $primaryKey = 'book_guid';
    protected $keyType = 'string';
    
    
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'books_categories', 'book_guid', 'category_guid', 'book_guid', 'guid');
    }
}
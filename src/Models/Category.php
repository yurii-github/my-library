<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

/**
 * @property-read string $guid primary key
 * @property string $title category title
 * 
 * @mixin Builder
 */
class Category extends Model
{
    public $timestamps = false;
    public $incrementing = false;
    protected $table='categories';
    protected $primaryKey = 'guid';
    protected $keyType = 'string';
    protected $fillable = ['title'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (self $book) {
            $book->guid = strtoupper(Uuid::uuid4());
        });
    }
    
    
}
<?php

namespace App\Models;

use App\Helpers\Tools;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

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

    protected static function boot()
    {
        parent::boot();

        static::creating(function (self $book) {
            $book->guid = Tools::com_create_guid();
        });
    }
    
    
}
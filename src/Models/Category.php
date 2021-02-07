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
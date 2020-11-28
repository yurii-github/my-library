<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $guid primary key
 * @property string $title
 */
class Category extends Model
{
    public $incrementing = false;
    protected $table='categories';
    protected $primaryKey = 'guid';
    protected $keyType = 'string';
}
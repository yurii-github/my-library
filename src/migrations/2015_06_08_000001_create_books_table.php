<?php
/*
 * My Book Library
 *
 * Copyright (C) 2014-2019 Yurii K.
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

use \Illuminate\Database\Migrations\Migration;
use \Illuminate\Database\Schema\Blueprint;
use \Illuminate\Database\Capsule\Manager;

class CreateBooksTable extends Migration
{
    public function up()
    {
        if (!Manager::schema()->hasTable('books')) {
            Manager::schema()->create('books', function (Blueprint $table) {
                $table->char('book_guid', 36);
                $table->primary('book_guid');
                $table->dateTime('created_date')->nullable();
                $table->dateTime('updated_date')->nullable();
                $table->binary('book_cover')->nullable();
                $table->decimal('book_cover', 3, 1)->default(0);
                $table->string('read', 3)->default('no');
                $table->integer('year');
                $table->string('title', 255);
                $table->string('isbn13', 255);
                $table->string('author', 255);
                $table->string('publisher', 255);
                $table->string('ext', 255);
                $table->text('filename');
            });
        }
    }


    public function down()
    {
        Manager::schema()->dropIfExists('books');
    }
}

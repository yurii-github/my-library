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

use \Illuminate\Database\Schema\Blueprint;
use \App\AppMigration;

class CreateBooksCategories extends AppMigration
{
    public function up()
    {
        if (!$this->getSchema()->hasTable('categories')) {
            $this->getSchema()->create('categories', function (Blueprint $table) {
                $table->char('guid', 36);
                $table->primary('guid');
                $table->string('title', 255);
            });
        }

        if (!$this->getSchema()->hasTable('books_categories')) {
            $this->getSchema()->create('books_categories', function (Blueprint $table) {
                $table->char('book_guid', 36);
                $table->char('category_guid', 36);
                $table->primary(['book_guid', 'category_guid']);
            });
        }
    }


    public function down()
    {
        $this->getSchema()->dropIfExists('books_categories');
        $this->getSchema()->dropIfExists('categories');
    }

}

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

use yii\db\Schema;
use yii\db\Migration;

class m150107_111042_01_init extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%books}}', [
            'book_guid' => 'CHAR(36) PRIMARY KEY',
            'created_date' => $this->dateTime()->null(),
            'updated_date' => $this->dateTime()->null(),
            'book_cover' => 'BLOB DEFAULT NULL',
            'favorite' => $this->decimal(3, 1)->notNull()->defaultValue(0),
            'read' => $this->string(3)->notNull()->defaultValue('no'),
            'year' => $this->integer(),
            'title' => $this->string(255),
            'isbn13' => $this->string(255),
            'author' => $this->string(255),
            'publisher' => $this->string(255),
            'ext' => $this->string(255),
            'filename' => $this->text(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%books}}');
    }
}

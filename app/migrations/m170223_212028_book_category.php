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

use yii\db\Migration;

class m170223_212028_book_category extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $isSQLite = strtolower($this->getDb()->getDriverName()) === 'sqlite';

        $this->createTable('{{%categories}}', [
            'guid' => 'CHAR(36) PRIMARY KEY',
            'title' => $this->string(255),
        ]);

        $columns = [
            'book_guid' => $this->char(36),
            'category_guid' => $this->char(36),
        ];

        $this->createTable('{{%books_categories}}', array_merge($columns, $isSQLite ? ['PRIMARY KEY (book_guid, category_guid)'] : []));

        if (!$isSQLite) {
            $this->addPrimaryKey('PK_book_category', '{{%books_categories}}', ['book_guid', 'category_guid']);
        }

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%categories}}');
        $this->dropTable('{{%books_categories}}');
    }

}

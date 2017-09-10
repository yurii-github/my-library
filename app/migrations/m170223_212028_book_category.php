<?php
use yii\db\Migration;
use yii\db\Schema;
use yii\db\ColumnSchemaBuilder;
use yii\db\ColumnSchema;
use yii\db\TableSchema;

class m170223_212028_book_category extends Migration
{
    public function up()
    {
        foreach (explode(';', file_get_contents(__DIR__ . '/schema/m170223_212028_book_category.sql')) as $query) {
            $this->execute($query);
        }
    }

    public function down()
    {
        $this->dropTable('{{%categories}}');
        $this->dropTable('{{%books_categories}}');
    }

}

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

        $tb_books = $this->getDb()->getSchema()->getTableSchema('{{%books}}');
        $flat = implode(',', array_filter(array_keys($tb_books->columns), 'is_string'));
        $this->execute("INSERT INTO books_tmp ($flat) SELECT $flat FROM {{%books}}");

        $this->dropTable('{{%books}}');
        $this->renameTable('books_tmp', '{{%books}}');
    }


    public function down()
    {
        $this->dropTable('{{%categories}}');
        $this->dropTable('{{%books_categories}}');
    }

}

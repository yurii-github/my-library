<?php
use yii\db\Migration;
use yii\db\Schema;

class m170223_212028_book_category extends Migration {

  public function up() {
    $this->createTable('{{%categories}}', [
      'category_guid' => 'CHAR(36) PRIMARY KEY', 
      'category_title' => 'VARCHAR(255)'
    ]);
    
    $this->addColumn('{{%books}}', 'category_guid', 'CHAR(36)');
    $this->addForeignKey('{{%books_categories_category_guid}}', 
        '{{%books}}', 'category_guid', 
        '{%categories}}', 'category_guid',
        'SET NULL', //on delete
        'CASCADE'); // on update
  }

  public function down() {
    $dbtype = $this->getDb()->getDriverName();
    
    // $this->dropTable('{{%categories}}');
    
    if ($dbtype == 'sqlite') {
      // drop column/FK not supported : http://www.sqlite.org/lang_altertable.html
      // CANNOT CLONE TABLE !!! https://github.com/yiisoft/yii2/issues/13651
      $tb_books = $this->getDb()->getSchema()->getTableSchema('{{%books}}');
      // unset($tb_books->columns['category_guid']);
      // $this->createTable('new_books', $tb_books->columns);
      // $this->createTableLike('new_books', $tb_books);
      var_dump($tb_books->columns);die;
    } else {
      $this->dropForeignKey('{{%books_categories_category_guid}}', '{{%books}}');
      $this->dropColumn('{{%books}}', 'category_guid');
    }
    
    
  }

}

<?php
use yii\db\Migration;
use yii\db\Schema;
use yii\db\ColumnSchemaBuilder;
use yii\db\ColumnSchema;
use yii\db\TableSchema;

class m170223_212028_book_category extends Migration {

  protected function columnToString(\yii\db\ColumnSchema $column) {
    $items = [];
    $items[] = strtoupper($column->dbType);
    $items[] = !$column->allowNull ? 'NOT NULL' : null;
    $items[] = $column->defaultValue == NULL ? 'DEFAULT NULL' : 'DEFAULT \''.$column->defaultValue.'\'';
    $items[] = $column->isPrimaryKey ? 'PRIMARY KEY' : null;
    
    return implode(' ', array_filter($items));
  }
  
  
  protected function cloneTable(TableSchema $tbl, $new_tbl_name, $options = '') {
    $columns = [];
    foreach ($tbl->columns as $n => $column) {
      $columns[$n] = $this->columnToString($column);
    }
    
    $this->createTable($new_tbl_name, $columns, $options);
    $flat = implode(',', $tbl->columnNames);
    $this->execute("INSERT INTO $new_tbl_name ($flat) SELECT $flat FROM $tbl->name");
    //?TODO: options
  }
  
  
  /**
   * 
   * {@inheritDoc}
   * @see \yii\db\Migration::up()
   */
  public function up() {
    $this->createTable('{{%categories}}', [
      'category_guid' => 'CHAR(36) PRIMARY KEY', 
      'category_title' => 'VARCHAR(255)'
    ]);
    
   $this->addColumn('{{%books}}', 'category_guid', 'CHAR(36)');
   // $tb_books = $this->getDb()->getSchema()->getTableSchema('{{%books}}');
   // $this->cloneTable($tb_books, '{{%books_tmp}}', 'FOREIGN KEY(category_guid) REFERENCES {{%categories}}(category_guid) ON DELETE SET NULL ON UPDATE CASCADE');
    

    if ($this->getDb()->getDriverName() == 'sqlite') {
    }
  }

  
  /**
   * 
   * {@inheritDoc}
   * @see \yii\db\Migration::down()
   */
  public function down() {
    if ($this->getDb()->getDriverName() == 'sqlite') {
      // drop column/FK not supported : http://www.sqlite.org/lang_altertable.html
      // CANNOT CLONE TABLE !!! https://github.com/yiisoft/yii2/issues/13651
      $tb_books = $this->getDb()->getSchema()->getTableSchema('{{%books}}');
      unset($tb_books->columns['category_guid']);
      $this->cloneTable($tb_books, '{{%books_tmp}}');
      $this->dropTable($tb_books->name);
      $this->renameTable('{{%books_tmp}}', $tb_books->name);
    } else {
      //$this->dropForeignKey('{{%books_categories_category_guid}}', '{{%books}}');
      $this->dropColumn('{{%books}}', 'category_guid');
    }
    
    $this->dropTable('{{%categories}}');
  }

}

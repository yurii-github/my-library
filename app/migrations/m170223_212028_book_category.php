<?php
use yii\db\Migration;

class m170223_212028_book_category extends Migration {

  public function up() {
    $this->createTable('{{%categories}}', [
      'category_guid' => 'CHAR(36) PRIMARY KEY', 
      'category_title' => 'VARCHAR(255)'
    ]);
    
    $this->addColumn('{{%books}}', 'category_guid', 'CHAR(36)');
  }

  public function down() {
    $this->dropTable($this->tbname);
    $this->dropColumn('{{%books}}', 'category_guid');
  }

}

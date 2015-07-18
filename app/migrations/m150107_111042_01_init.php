<?php
use yii\db\Schema;
use yii\db\Migration;

class m150107_111042_01_init extends Migration
{

	private $tbname = '{{%books}}';

	public function safeUp()
	// public function up()
	{
		//skip if table exist
		if (!empty($this->db->getTableSchema($this->tbname))) {
			return;
		}
		
		$tableOptions = null;
		
		$this->createTable($this->tbname, [
			'book_guid' => 'CHAR(36) PRIMARY KEY',
			'created_date' => 'DATETIME NOT NULL',
			'updated_date' => 'DATETIME NOT NULL',
			'book_cover' => 'BLOB DEFAULT NULL',
			'favorite' => 'DECIMAL(3,1) NOT NULL DEFAULT 0',
			'read' => "VARCHAR(3) NOT NULL DEFAULT 'no'",
			'year' => 'INT',
			'title' => 'VARCHAR(255)',
			'isbn13' => 'VARCHAR(255)',
			'author' => 'VARCHAR(255)',
			'publisher' => 'VARCHAR(255)',
			'ext' => 'VARCHAR(5)',
			'filename' => 'TEXT'
		], $tableOptions);
	}

	public function safeDown()
	// public function down()
	{
		$this->dropTable($this->tbname);
		// return false; //false if cannot rollback
	}
}

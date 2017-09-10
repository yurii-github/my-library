<?php
use yii\db\Schema;
use yii\db\Migration;

class m150107_111042_01_init extends Migration
{
	public function safeUp()
	{
        foreach (explode(';',file_get_contents(__DIR__.'/schema/m150107_111042_01_init.sql')) as $query) {
            $this->execute($query);
        }
	}

}

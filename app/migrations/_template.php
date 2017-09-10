<?php
echo "<?php\n";
?>
class <?=$className?> extends \yii\db\Migration
{
	private $tbname = '{{%books}}';

    public function safeUp()
    {
    }

    public function safeDown()
    {
        echo "<?=$className?> cannot be reverted.\n";

        return false;
    }
}

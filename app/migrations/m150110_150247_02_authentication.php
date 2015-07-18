<?php
class m150110_150247_02_authentication extends \yii\db\Migration
{
	private $tbname = '{{%users}}';

	public function createTable_Users()
	{
		$this->createTable($this->tbname, [
			'username' => 'VARCHAR(255) PRIMARY KEY',
			'password' => 'VARCHAR(255)',
			'access_token' => 'VARCHAR(2000)',
			'auth_key' => 'VARCHAR(255)'
		]);
	}
	
	
    public function safeUp()
    {
    	//run first!!!!
    	//C:\Users\Yurii\PHP-Developer\WebApplications\new-mylib>yii2 migrate --migrationPath=@yii/rbac/migrations
    	$a = \Yii::$app->authManager;
    	//
    	//create permissions
    	$perms = ['list-books', 'edit-books'];
    	foreach ($perms as $p) {
    		$a->add($a->createPermission($p));
    	}
    	//
    	// create roles and assign permissions
    	// --users
    	$r = $a->createRole('users');
    	$r->description = 'general users';
    	$a->add($r);
    	$a->addChild($r, $a->getPermission('list-books'));
    	// --admins
    	$r = $a->createRole('admins');
    	$r->description = 'full rights';
    	$a->add($r);
    	$a->addChild($r, $a->getPermission('list-books'));
    	$a->addChild($r, $a->getPermission('edit-books'));
    	//
    	
    	
    	$this->createTable_Users();
    	
    	$this->batchInsert($this->tbname, ['username', 'password'], [
    		['root', \Yii::$app->getSecurity()->generatePasswordHash('root')] ]);
    	
    	$a->assign($a->getRole('users'), 'root');
    }

    public function safeDown()
    {
    	$a = \Yii::$app->authManager;
    	$a->removeAll();
    	
    	$this->dropTable($this->tbname);
    }
}

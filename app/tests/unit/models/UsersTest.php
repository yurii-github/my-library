<?php
namespace tests\models;

use app\models\Users;

class UsersTest extends \tests\AppTestCase
{
	protected function setUp()
	{
		$this->mockYiiApplication(
			[
			/*	'components' => [
				'user'=> [
					'identityClass' => 'app\models\Users',
					'enableAutoLogin' => true,
					'loginUrl' => ['site/index'],
					'enableSession' => true
				], ]*/
			]
			
			);//['components' => [ 'authManager' => '\yii\rbac\DbManager' ] ]);
		
		//TODO: rbac checks
		//foreach ( explode(';', file_get_contents(\Yii::getAlias('@yii/rbac/migrations/schema-sqlite.sql'))) as $query ) {
		//	$this->getPdo()->exec($query);
		//}
		
		//WTF! refactor
		require_once $GLOBALS['basedir'] . '/console/migrations/m150110_150247_02_authentication.php';
		$m = new \m150110_150247_02_authentication(['db' => \Yii::$app->db]);
		ob_start();
		$m->createTable_Users();
		ob_end_clean();
	}
	
	public function test_finds()
	{
		$user = new Users();
		$user->username = 'yurii';
		$user->password = 'pass';
		$user->access_token = 'token';
		$user->save();
		
		$user2 = new Users();
		$user2->username = 'yurii2';
		$user2->password = 'pass2';
		$user2->access_token = 'token2';
		$user2->save();
		
		$this->assertEquals($user->username, Users::findIdentity($user->username)->username);
		$this->assertEquals($user->username, Users::getUserByUsername($user->username)->username);
		$this->assertEquals($user->access_token, Users::findIdentityByAccessToken($user->access_token)->access_token);
	}
	
	
}
<?php
namespace tests\models;

use app\models\Users;

class UsersTest extends \tests\AppTestCase
{
	protected function setUp()
	{
		
		$this->mockYiiApplication();
		parent::setUp();
		
			
			/*	'components' => [
				'user'=> [
					'identityClass' => 'app\models\Users',
					'enableAutoLogin' => true,
					'loginUrl' => ['site/index'],
					'enableSession' => true
				], ]*/
			
			
			//['components' => [ 'authManager' => '\yii\rbac\DbManager' ] ]);
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
		
		$this->assertEquals($user->username, $user->getId());
		$this->assertEquals($user->username, Users::findIdentity($user->username)->username);
		$this->assertEquals($user->username, Users::getUserByUsername($user->username)->username);
		$this->assertEquals($user->access_token, Users::findIdentityByAccessToken($user->access_token)->access_token);
	}
	
	
	public function test_validates()
	{
		$user = new Users();
		$user->username = 'yurii';
		$user->password = 'pass';
		$user->access_token = 'token';
		$user->save();
		
		/* @var $user_db Users */
		$user_db = Users::getUserByUsername('yurii');
		$this->assertTrue($user_db->validatePassword('pass'));
		$this->assertTrue($user_db->validateAuthKey($user->auth_key));
	}
	
	
}
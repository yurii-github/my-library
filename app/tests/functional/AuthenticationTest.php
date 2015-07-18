<?php
namespace tests\functional;

use app\models\Users;

//use app\models\Books;
class AuthenticationTest extends \tests\AppFunctionalTestCase
{
	/***
	 * @var \app\controllers\SiteController
	 */
	private $controllerSite;
	
	
	protected function setUp()
	{
		$_SERVER['REQUEST_URI'] = 'index.php';
		
		parent::setUp();
		
		$this->controllerSite = \Yii::$app->createControllerByID('site');
		
		$user = new Users();
		$user->username = $user->password = 'root';
		$user->save();
	}
	
	
	function test_Login_WrongUsername()
	{
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$_POST['username'] = 'wrong-login';
		$_POST['password'] = 'root';
		
		$this->assertNull(\Yii::$app->user->identity); // guest
		$r = json_decode($this->controllerSite->runAction('login'));
		$this->assertNull(\Yii::$app->user->identity); // guest
		$this->assertFalse($r->result);
		$this->assertEquals('wrong login or password', $r->data);
	}
	
	
	function test_Login_WrongPassword()
	{
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$_POST['username'] = 'root';
		$_POST['password'] = 'wrong-password';
		
		$this->assertNull(\Yii::$app->user->identity); // guest
		$r = json_decode($this->controllerSite->runAction('login'));
		$this->assertNull(\Yii::$app->user->identity); // guest
		$this->assertFalse($r->result);
		$this->assertEquals('wrong login or password', $r->data);
	}
	
	
	function test_Login_Success()
	{
		//$this->markTestIncomplete();
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$_POST['username'] = 'root';
		$_POST['password'] = 'root';
		
		$this->assertNull(\Yii::$app->user->identity); // guest
		$r = json_decode($this->controllerSite->runAction('login'));
		$this->assertInstanceOf(\app\models\Users::class, \Yii::$app->user->identity); // logged in
		$this->assertTrue($r->result);
	}
	

	function test_Logout_Success()
	{
		//$this->markTestIncomplete();
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$_POST['username'] = 'root';
		$_POST['password'] = 'root';
	
		$r = json_decode($this->controllerSite->runAction('login'));
		$this->assertInstanceOf(\app\models\Users::class, \Yii::$app->user->identity); // logged in
		
		//try logout
		unset($_POST);
		$r = json_decode($this->controllerSite->runAction('logout'));
		$this->assertNull(\Yii::$app->user->identity); // guest
		///$r = json_decode($this->controllerSite->runAction('login'));
		//
		//$this->assertTrue($r->result);
	}
	
	
}
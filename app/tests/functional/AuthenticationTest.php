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
		
		$r = json_decode($this->controllerSite->runAction('login'));
		
		$this->assertFalse($r->result);
		$this->assertEquals('wrong login or password', $r->data);
	}
	
	
	function test_Login_WrongPassword()
	{
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$_POST['username'] = 'root';
		$_POST['password'] = 'wrong-password';
		
		$r = json_decode($this->controllerSite->runAction('login'));
		
		$this->assertFalse($r->result);
		$this->assertEquals('wrong login or password', $r->data);
	}
	
	
	function test_Login()
	{
		//$this->markTestIncomplete();
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$_POST['username'] = 'root';
		$_POST['password'] = 'root';
		
		$r = json_decode($this->controllerSite->runAction('login'));
		
		$this->assertTrue($r->result);
	}
	
	
}
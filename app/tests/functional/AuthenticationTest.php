<?php
namespace tests\functional;

//use app\models\Books;
class AuthenticationTest extends \tests\AppFunctionalTestCase
{
	
	
	function test_Login()
	{
		$this->markTestIncomplete('not yet');
		
		$_SERVER['REQUEST_URI'] = 'index.php';
		
		/* @var $controller \app\controllers\SiteController */
		$controller = \Yii::$app->createControllerByID('site');
		$r = $controller->runAction('logout');
		
		var_dump($r);
		
	}
	
	
}
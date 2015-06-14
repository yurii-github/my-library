<?php
namespace tests\components;

use app\components\Security;

class SecurityTest extends \tests\AppTestCase
{
	public $security;
	
	public function setUp()
	{
		parent::setUp();
		
		$this->security = new Security();
	}
	
	/**
	 * @expectedException yii\base\ErrorException
	 */
	public function test_badAlgorythm()
	{
		$this->security->hashAlgorithm = 'bad algorythm';
		$this->security->generatePasswordHash('pass');
	}
	
	/**
	 * @expectedException yii\base\ErrorException
	 */
	public function test_badPassword()
	{
		$this->security->generatePasswordHash(['bad pass']);
	}

	public function test_password()
	{
		$password = 'pass';
		$hash = $this->security->generatePasswordHash($password);
		
		$this->assertNotEmpty($hash);
		$this->assertTrue($this->security->validatePassword($password, $hash));
	}
	
}
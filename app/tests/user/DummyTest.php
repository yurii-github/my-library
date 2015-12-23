<?php

class DummyTest extends PHPUnit_Extensions_Selenium2TestCase
{
	protected function setUp()
	{
		$this->setBrowser('chrome');
		if (getenv('TRAVIS')) { // running on TRAVIS CI
			$this->setBrowserUrl('http://127.0.0.1:8888');
		} else {
			$this->setBrowserUrl('http://localhost/mylibrary-yii2/app/public/');
		}
		
	}
	
	public function testTitle()
	{
		$this->url('');
		$this->assertEquals('MyLibrary ~ Books', $this->title());
	}
	
	
}
<?php

class DummyTest extends PHPUnit_Extensions_Selenium2TestCase
{
	protected function setUp()
	{
		$this->setBrowser('chrome');
		$this->setBrowserUrl('http://127.0.0.1:8888');
	}
	
	public function testTitle()
	{
		$this->url('');
		$this->assertEquals('MyLibrary ~ Books', $this->title());
	}
	
	
}
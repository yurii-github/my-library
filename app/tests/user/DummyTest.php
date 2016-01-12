<?php

namespace tests\user;

class DummyTest extends \tests\AppUserTestCase
{
	public function test_Title()
	{
		$driver = RemoteWebDriver::create($this->selenium2_hub, DesiredCapabilities::chrome());
		//$driver->w
		$driver->get($this->website_base);
		$this->assertEquals('MyLibrary ~ Books', $driver->getTitle());
		$driver->close();
	}
	
	/*
	protected function setUp()
	{
		//$this->website_base = 'http://127.0.0.1:8888';
		//return;
		
		if (!empty(getenv('TRAVIS'))) { // running on TRAVIS CI
			$this->website_base = 'http://127.0.0.1:8888';
		} else {
			$this->website_base = 'http://localhost/mylibrary-yii2/app/public/';
		}
		
		$this->website_base = 'http://127.0.0.1:80/mylibrary-yii2/app/public/';
		$this->website_base = 'http://127.0.0.1:80';
	}
	
	public function testTitle()
	{

		$driver = RemoteWebDriver::create($this->selenium2_hub, DesiredCapabilities::chrome());
		//$driver->w
		$driver->get($this->website_base);
		$this->assertEquals('MyLibrary ~ Books', $driver->getTitle());
		$driver->close();
	}
	*/
	
}
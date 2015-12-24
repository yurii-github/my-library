<?php

namespace tests\user;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;

class DummyTest extends \PHPUnit_Framework_TestCase
{
	public $selenium2_hub = 'http://127.0.0.1:4444/wd/hub';
	public $website_base; 
	
	protected function setUp()
	{
		if (getenv('TRAVIS')) { // running on TRAVIS CI
			$this->website_base = 'http://127.0.0.1:8888';
		} else {
			$this->website_base = 'http://localhost/mylibrary-yii2/app/public/';
		}
		
	}
	
	public function testTitle()
	{

		$driver = RemoteWebDriver::create($this->selenium2_hub, DesiredCapabilities::chrome());
		
		$driver->get($this->website_base);
		$this->assertEquals('MyLibrary ~ Books', $driver->getTitle());
		$driver->close();
	}
	
	
}
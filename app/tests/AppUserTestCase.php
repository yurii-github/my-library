<?php
namespace tests;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;

class AppUserTestCase extends \PHPUnit_Framework_TestCase
{
	const HUB = 'http://127.0.0.1:4444/wd/hub';
	
	protected $WEBSITE;
	protected $driver;
	
	protected function setUp()
	{
		$this->driver = RemoteWebDriver::create(self::HUB, DesiredCapabilities::chrome());
		
		if (!empty(getenv('TRAVIS'))) { // running on TRAVIS CI
			$this->WEBSITE = 'http://127.0.0.1:8888';
		} else {
			$this->WEBSITE = 'http://localhost/mylibrary-yii2/app/public/';
		}
	}
	
	protected function tearDown()
	{
		$this->driver->close();
	}
	
	
	public function test_Title()
	{
		$driver = RemoteWebDriver::create($this->selenium2_hub, DesiredCapabilities::chrome());
		//$driver->w
		$driver->get($this->website_base);
		$this->assertEquals('MyLibrary ~ Books', $driver->getTitle());
		$driver->close();
	}
	
}
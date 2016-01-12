<?php
namespace tests;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;

class AppUserTestCase extends \PHPUnit_Framework_TestCase
{
	private $WEBSITE;
	private $BROWSER;
	
	protected $driver;
	
	protected function setUp()
	{
		if (!empty(getenv('TRAVIS'))) { // running on TRAVIS CI
			$this->WEBSITE = 'http://127.0.0.1:8080';
			$this->BROWSER = DesiredCapabilities::chrome();
		} else {
			$this->WEBSITE = 'http://localhost/mylibrary-yii2/app/public/';
			$this->BROWSER = DesiredCapabilities:: chrome();
		}
		
		$this->driver = RemoteWebDriver::create('http://127.0.0.1:4444/wd/hub', $this->BROWSER);
	}
	
	protected function tearDown()
	{
		$this->driver->close();
	}

	public function getSiteUrl()
	{
		return $this->WEBSITE;
	}
}
<?php
namespace tests;

//https://github.com/facebook/php-webdriver
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use PHPUnit\Framework\TestCase;

class AppUserTestCase extends TestCase
{
	private $WEBSITE;
	private $BROWSER;

	private static $driver;

	protected function setUp(): void
	{
		if (!empty(getenv('TRAVIS'))) { // running on TRAVIS CI
			$this->WEBSITE = 'http://127.0.0.1:8080';
			$this->BROWSER = DesiredCapabilities::firefox();
		} else {
			$this->WEBSITE = 'http://localhost/mylibrary-yii2/app/public/';
			$this->BROWSER = DesiredCapabilities:: chrome();
		}


	}

	protected function tearDown(): void
	{
		//self::$driver->
		$this->getDriver()->close();
		self::$driver = null;
	}

	public function getDriver()
	{
		if (self::$driver == null) {
			self::$driver = RemoteWebDriver::create('http://127.0.0.1:4444/wd/hub', $this->BROWSER);
		}

		return self::$driver;
	}

	public function getSiteUrl()
	{
		return $this->WEBSITE;
	}
}

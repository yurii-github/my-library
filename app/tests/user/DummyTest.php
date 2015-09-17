<?php

class DummyTest extends PHPUnit_Framework_TestCase
{
	
	public function test_header()
	{
		putenv('foo=bar');
		$w = RemoteWebDriver::create('http://localhost:4444/wd/hub', [
			WebDriverCapabilityType::BROWSER_NAME => WebDriverBrowserType::HTMLUNIT
		]);
		$url = 'http://localhost/mylibrary-yii2/backend/web/site/index';
		$w->get($url);
		echo $w->getPageSource();

		//$w->takeScreenshot('1.jpg');
		
		$this->assertTrue(true);

		$w->quit();
	}
}
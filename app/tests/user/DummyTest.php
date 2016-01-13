<?php
namespace tests\user;

use Facebook\WebDriver\WebDriverBy as By;
use Facebook\WebDriver\WebDriverSelect as Select;
use app\components\Configuration;

class DummyTest extends \tests\AppUserTestCase
{

	public function test_DefaultConfigPage()
	{
		$drv = $this->getDriver()->get($this->getSiteUrl().'/config');
		
		$cfg = json_decode(Configuration::DEFAULT_CONFIG_JSON, true);
		$SUPPORTED_VALUES = Configuration::SUPPORTED_VALUES; //PDT 3.4 fails to understand const array support. TODO: bug report
				
		$this->assertEquals('MyLibrary ~ Configuration', $drv->getTitle());

		// === SELECTS ===
		//
		//-- system_language
		$el = new Select($drv->findElement(By::xpath("//select[@name='system_language']")));		
		$this->assertEquals($cfg['system']['language'], $el->getFirstSelectedOption()->getAttribute('value')); //def value
		$this->assertEquals($SUPPORTED_VALUES['system_language'][$cfg['system']['language']], $el->getFirstSelectedOption()->getText()); //def txt
		// all options TODO: as function
		foreach ($SUPPORTED_VALUES['system_language'] as $k => $v) {
			$found = false;
			foreach ($el->getOptions() as $opt) {
				if ($opt->getAttribute('value') == $k) {
					if ($opt->getText() != $v) {
						$this->fail("value '$k' mached, but visible config text '$v' does not match on page '" . $opt->getText() . "'");
					}
					$found = true;
					break;
				}
			}
			if (!$found) {
				$this->fail("value $k was not generated on page");
			}
		}
		//
		//-- system_theme
		$el = new Select($drv->findElement(By::xpath("//select[@name='system_theme']")));
		$this->assertEquals($cfg['system']['theme'], $el->getFirstSelectedOption()->getAttribute('value')); //def value
		$this->assertEquals($cfg['system']['theme'], $el->getFirstSelectedOption()->getText()); //def txt, equals to value
		//
		//-- system_timezone
		$el = new Select($drv->findElement(By::xpath("//select[@name='system_timezone']")));
		$this->assertEquals($cfg['system']['timezone'], $el->getFirstSelectedOption()->getAttribute('value')); //def value
		$this->assertEquals($cfg['system']['timezone'], $el->getFirstSelectedOption()->getText()); //def txt, equals to value
		
	}
	
	
}


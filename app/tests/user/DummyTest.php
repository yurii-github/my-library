<?php

namespace tests\user;

class DummyTest extends \tests\AppUserTestCase
{
	public function test_Title()
	{
		$this->driver->get($this->getSiteUrl());
		
		var_dump($this->getSiteUrl());
		
		echo $this->driver->getPageSource(); die;
		$this->assertEquals('MyLibrary ~ Books', $this->driver->getTitle());
	}
	
}
<?php

namespace tests\user;

class DummyTest extends \tests\AppUserTestCase
{
	public function test_Title()
	{
		$this->driver->get($this->WEBSITE);
		$this->assertEquals('MyLibrary ~ Books', $this->driver->getTitle());
	}
	
}
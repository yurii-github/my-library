<?php
namespace tests\functional;


class MigrationTest extends \tests\AppFunctionalTestCase
{
	
	public function test_MigrationInstall()
	{
		try {
			$this->cleanDb();
			$this->mockYiiApplication();
			
			/* @var $controller \app\controllers\InstallController */
			$c = $this->mockController('install');
			$r = $c->runAction('migrate');
			$this->assertEquals('//site/migration', $r[0], 'render view does not match');
			$this->assertTrue($r[1]['result'], "migration has failed with content: \n\n". $r[1]['content']);
		} finally {
			$this->resetConnection();
		}

	}	
	
}
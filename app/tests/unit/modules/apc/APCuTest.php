<?php
namespace tests\modules\apc;

use modules\apc\models\APCu;

class APCuMock extends APCu
{
	public function __construct() {}
	
	public function mock_getDuration($ts)
	{	
		return $this->getDuration($ts);
	}
}


class APCuTest extends \tests\AppTestCase
{
	public function test_bsize()
	{
		$this->assertEquals('1.0 Kbyte', APCu::bsize(1024));
		$this->assertEquals('2.0 Kbytes', APCu::bsize(2*1024));
		
		$this->assertEquals('1.0 Mbyte', APCu::bsize(1024*1024));
		$this->assertEquals('2.0 Mbytes', APCu::bsize(2*1024*1024));
		
		$this->assertEquals('1.0 Gbyte', APCu::bsize(1024*1024*1024));
		$this->assertEquals('2.0 Gbytes', APCu::bsize(2*1024*1024*1024));
	}
	
	
	public function test_getDuration()
	{
		$apcu_mock = new APCuMock();
		$duration_text = $apcu_mock->mock_getDuration(
			(new \DateTime())->add(new \DateInterval('P10D')) // +10 days
		);
		
		$this->assertRegExp('/00 y 00 m 10 d 00 h 00 m 00 s/', $duration_text);
	}
}
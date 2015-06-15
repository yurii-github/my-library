<?php
namespace tests\components\log;

use app\components\ApcCache;
use yii\log\Logger;

class FileTargetTest extends \tests\AppTestCase
{
	public function test_formatMessage()
	{
		//txt, lvl, category, timestamp
		$message =	['some message', Logger::LEVEL_INFO, 'app', (new \DateTime('2001-02-03 04:05:06'))->getTimestamp()+0.12345];
		
		$this->initAppFileSystem();
		$ft = new \yii\log\FileTarget(['logFile' => '@app/runtime/logs/log.txt']);
		$ft_my = new \app\components\log\FileTarget(['logFile' => '@app/runtime/logs/log_my.txt']);

		$this->assertEquals('2001-02-03 04:05:06 [info][app] some message', $ft->formatMessage($message)); //yii2 default
		$ft_my->with_microtime = false;
		$this->assertEquals('2001-02-03 04:05:06 [info][app] some message', $ft_my->formatMessage($message)); //mylib as yii2 default
		$ft_my->with_microtime = true;
		$this->assertEquals('2001-02-03 04:05:06:123450 [info][app] some message', $ft_my->formatMessage($message)); //mylib custom with 6 nums pad
	}
	
	
	public function test_formatMessage_varDump()
	{
		$message =	[ ['key' => 'value'], Logger::LEVEL_INFO, 'app', (new \DateTime('2001-02-03 04:05:06'))->getTimestamp()+0.12345];
		
		$this->initAppFileSystem();
		$ft_my = new \app\components\log\FileTarget(['logFile' => '@app/runtime/logs/log_my.txt']);
		
		$this->assertEquals("2001-02-03 04:05:06 [info][app] [
    'key' => 'value',
]", $ft_my->formatMessage($message));
		
	}
	
	public function test_formatMessage_Trace()
	{
		$message =	['some message', Logger::LEVEL_INFO, 'app', (new \DateTime('2001-02-03 04:05:06'))->getTimestamp()+0.12345, [['file' => '1', 'line' => '2']]];
	
		$this->initAppFileSystem();
		$ft_my = new \app\components\log\FileTarget(['logFile' => '@app/runtime/logs/log_my.txt']);
	
		$this->assertEquals("2001-02-03 04:05:06 [info][app] some message
    in 1:2", $ft_my->formatMessage($message));
	
	}
	
}

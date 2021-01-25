<?php

namespace Tests;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

class VirtualFileSystemTestXXX extends TestCase
{
    public function testRealFS()
    {
        $filename = '/tmp/a.txt';
        
        $stream1 = fopen($filename, 'a+');
        $result1 = flock($stream1, LOCK_EX | LOCK_NB, $lock1);
        $stream2 = fopen($filename, 'a+');
        $result2 = flock($stream2, LOCK_EX | LOCK_NB, $lock2);
        
        $this->assertFileExists($filename);
        $this->assertSame(0, $lock1);
        $this->assertTrue($result1);
        $this->assertSame(1, $lock2);
        $this->assertFalse($result2);
    }

    public function testVirtualFS()
    {
        $root = vfsStream::setup();
        $filename = vfsStream::newFile('a.txt')->at($root)->url();

        $stream1 = fopen($filename, 'a+');
        $result1 = flock($stream1, LOCK_EX | LOCK_NB, $lock1);
        $stream2 = fopen($filename, 'a+');
        $result2 = flock($stream2, LOCK_EX | LOCK_NB, $lock2);

        $this->assertFileExists($filename);
        $this->assertSame(0, $lock1);
        $this->assertTrue($result1);
        $this->assertSame(1, $lock2); // this FAILS, because it is 0
        $this->assertFalse($result2); // this passes
    }
}
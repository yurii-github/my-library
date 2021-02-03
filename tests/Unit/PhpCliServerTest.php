<?php

namespace Tests\Unit;

use App\PhpCliServer;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

/**
 * @runTestsInSeparateProcesses
 */
class PhpCliServerTest extends TestCase
{
    protected $webDir;

    protected function setUp(): void
    {
        vfsStream::setup('base/webdir');
        $this->webDir = vfsStream::url('base/webdir');
    }

    public function testIsNotCliServerByDefault()
    {
        $this->assertFalse(PhpCliServer::isCliServer());
        $this->assertSame('cli', PHP_SAPI);
    }

    public function testSkipsIfNotCliServer()
    {
        ob_start();
        $result = PhpCliServer::handle($this->webDir);
        $content = ob_get_clean();

        $this->assertFalse($result);
        $this->assertSame('', $content);
    }

    public function testSkipsPath()
    {
        $cli = $this->enforcedCliServer();
        $url = $this->fakeServerRequest("/something");

        ob_start();
        $result = $cli::handle($this->webDir);
        $content = ob_get_clean();

        $this->assertFalse($result);
        $this->assertSame('', $content);
    }

    public function testSkipsUnsupportedFiles()
    {
        $cli = $this->enforcedCliServer();
        $url = $this->fakeServerRequest("/1.txt");

        ob_start();
        $result = $cli::handle($this->webDir);
        $content = ob_get_clean();

        $this->assertFalse($result);
        $this->assertSame('', $content);
    }

    /**
     * @dataProvider providesSupportedFilenames
     * @param string $filename
     */
    public function testServesSupportedStaticFileWithFile(string $filename)
    {
        $cli = $this->enforcedCliServer();
        $this->assertTrue($cli::isCliServer());

        file_put_contents($this->webDir . '/' . $filename, 'some data');
        $this->assertFileExists($this->webDir . '/' . $filename);

        $url = $this->fakeServerRequest("/{$filename}");

        ob_start();
        $result = $cli::handle($this->webDir);
        $content = ob_get_clean();

        $this->assertTrue($result);
        $this->assertSame('some data', $content);
    }

    /**
     * @dataProvider providesSupportedFilenames
     */
    public function testServesSupportedStaticFilesWithoutFileIfCliServer(string $filename)
    {
        $cli = $this->enforcedCliServer();
        $this->assertTrue($cli::isCliServer());

        $url = $this->fakeServerRequest("/{$filename}");

        ob_start();
        $result = $cli::handle($this->webDir);
        $content = ob_get_clean();

        $this->assertSame("'/{$filename}' does not exist", $content);
    }


    public function providesSupportedFilenames()
    {
        return [
            ['file.png'],
            ['file.js'],
            ['file.jpg'],
            ['file.jpeg'],
            ['file.gif'],
            ['file.css'],
            ['file.ico'],
            ['file.jpg'],
            ['file.jpg'],
        ];
    }


    protected function fakeServerRequest($path)
    {
        $_SERVER['HTTP_HOST'] = 'localhost';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['SERVER_NAME'] = 'localhost';
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
        $_SERVER['REQUEST_URI'] = $path;

        return 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }

    protected function enforcedCliServer(): PhpCliServer
    {
        $cli = new class() extends PhpCliServer {
            public static function isCliServer()
            {
                return true;
            }
        };
        $this->assertTrue($cli::isCliServer());

        return $cli;
    }
}
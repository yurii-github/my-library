<?php

namespace Tests\Functional;

class PagesTest extends AbstractTestCase
{
    public function testConfigPage()
    {
        $request = $this->createRequest('GET', '/config');
        $response = $this->app->handle($request);
        $response->getBody()->rewind();
        $content = $response->getBody()->getContents();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringContainsString('</html>', $content);
        $this->assertStringContainsString('<title>MyLibrary ~ Configuration</title>', $content);
    }


    public function testIndexPage()
    {
        $request = $this->createRequest('GET', '/');
        $response = $this->app->handle($request);
        $response->getBody()->rewind();
        $content = $response->getBody()->getContents();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringContainsString('</html>', $content);
        $this->assertStringContainsString('<title>MyLibrary ~ Books</title>', $content);
    }


    public function testAboutPage()
    {
        $request = $this->createRequest('GET', '/about');
        $response = $this->app->handle($request);
        $response->getBody()->rewind();
        $content = $response->getBody()->getContents();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringContainsString('</html>', $content);
        $this->assertStringContainsString('<title>MyLibrary ~ About Project</title>', $content);
    }

}
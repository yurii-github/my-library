<?php

namespace Tests\Functional;

use App\AppMigrator;
use App\Configuration\Configuration;
use Http\Factory\Guzzle\ServerRequestFactory;
use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Testing\InteractsWithDatabase;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use \App\Bootstrap;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Slim\App;

abstract class AbstractTestCase extends TestCase
{
    use InteractsWithDatabase;

    /** @var App */
    protected $app;
    /** @var Manager */
    protected $db;


    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->initVirtualFileSystem();
        $this->initConfig();
        $this->app = Bootstrap::initApplication(vfsStream::url('base/data'));
        $migrator = new AppMigrator(Container::getInstance()->get(Migrator::class));
        $output = $migrator->migrate();

        $this->db = $this->app->getContainer()->get('db');
        assert($this->db instanceof Manager);
        $this->db->getConnection()->beginTransaction();
    }

    protected function tearDown(): void
    {
        $this->db->getConnection()->rollBack();
    }
    
    
    protected function getConnection($connection = null)
    {
        return $this->db->getConnection($connection);
    }


    /**
     * Create a server request.
     *
     * @param string $method The HTTP method
     * @param string|UriInterface $uri The URI
     * @param array $serverParams The server parameters
     * @return ServerRequestInterface
     */
    protected function createRequest(string $method, $uri, array $serverParams = []): ServerRequestInterface
    {
        return (new ServerRequestFactory())->createServerRequest($method, $uri, $serverParams);
    }


    /**
     * Create a JSON request.
     *
     * @param string $method The HTTP method
     * @param string|UriInterface $uri The URI
     * @param array|null $data The json data
     *
     * @return ServerRequestInterface
     */
    protected function createJsonRequest(string $method, $uri, array $data = null): ServerRequestInterface
    {
        $request = $this->createRequest($method, $uri);
        if ($data !== null) {
            $request = $request->withParsedBody($data);
        }
        return $request->withHeader('Content-Type', 'application/json');
    }

    /**
     * Verify that the given array is an exact match for the JSON returned.
     *
     * @param array $expected The expected array
     * @param ResponseInterface $response The response
     * @return void
     */
    protected function assertJsonData(array $expected, ResponseInterface $response): void
    {
        $actual = (string)$response->getBody();
        $this->assertSame($expected, (array)json_decode($actual, true, 512));
    }
    
    protected function useSqliteInMemory(\stdClass $config)
    {
        $config->database->filename = ':memory:';
    }
    
    protected function useSqliteInFile(\stdClass $config)
    {
        // SQLite does not support streams https://github.com/bovigo/vfsStream/issues/19
        $testDbFilename = dirname(__DIR__) . '/data/mytestdb.s3db';
        $config->database->filename = $testDbFilename;
        if (file_exists($testDbFilename)) {
            unlink($testDbFilename);
        }
        touch($testDbFilename);
    }
    
    protected function initVirtualFileSystem()
    {
        vfsStream::setup('base', null, [
            'data' => [
                'books' => [],
                'logs' => [],
            ],
        ]);
    }

    protected function initConfig()
    {
        $config = json_decode(file_get_contents(dirname(__DIR__) . '/data/config_sqlite.json'));
        //$this->useSqliteInFile($config);
        $this->useSqliteInMemory($config);
        file_put_contents(vfsStream::url('base/data/config.json'), json_encode($config, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }

    
    protected function getLibraryConfig(): Configuration
    {
        return $this->app->getContainer()->get(Configuration::class);
    }

    
    protected function setBookLibrarySync(bool $mode): void
    {
        $this->getLibraryConfig()->getLibrary()->sync = $mode;
    }
}

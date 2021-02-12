<?php
/*
 * My Book Library
 *
 * Copyright (C) 2014-2021 Yurii K.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see http://www.gnu.org/licenses
 */

namespace App;

use App\Configuration\Configuration;
use App\Handlers\ErrorHandler;
use App\Handlers\ShutdownHandler;
use App\Renderers\JsonErrorRenderer;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Events\Dispatcher;
use Illuminate\Translation\ArrayLoader;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Slim\App;
use Slim\Factory\AppFactory;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Translation\Loader\PhpFileLoader;
use Symfony\Component\Translation\Translator;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;
use Illuminate\Database\Migrations\MigrationRepositoryInterface;
use \Illuminate\Container\Container;
use \Illuminate\Contracts\Container\Container as ContainerInterface;
use Illuminate\Contracts\Events\Dispatcher as EventDispatcherInterface;
use \Illuminate\Database\Migrations\DatabaseMigrationRepository;
use \Illuminate\Database\Migrations\Migrator;
use \Illuminate\Filesystem\Filesystem;
use \Illuminate\Translation\Translator as IlluminateTranslator;

class Bootstrap
{
    const CURRENT_APP_VERSION = '2.0';
    public const DEBUG_MODE = true;

    protected static function initCapsule(Configuration $config, Container $container, Dispatcher $eventDispatcher): Manager
    {
        $capsule = new Manager($container);
        $capsule->addConnection([
            'driver' => $config->database->format,
            'host' => $config->database->host,
            'database' => $config->database->format === 'sqlite' ? $config->database->filename : $config->database->dbname,
            'username' => $config->database->login,
            'password' => $config->database->password,
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
        ]);

        Model::clearBootedModels();
        Model::setConnectionResolver($capsule->getDatabaseManager());
        Model::setEventDispatcher($eventDispatcher);

        $pdo = $capsule->getConnection()->getPdo();
        if ($pdo->getAttribute(\PDO::ATTR_DRIVER_NAME) === 'sqlite') {
            // not documented feature of SQLite - add case insensitive search
            $pdo->sqliteCreateFunction('like', function ($x, $y) {
                // Example: $x = '%ч'; $y = 'Чasd';
                $x = str_replace('%', '', $x);
                $x = preg_quote($x);
                $matched = preg_match('/' . $x . '/iu', $y);
                return (bool)$matched;
            });
        }

        return $capsule;
    }

    public static function initEnvironment(string $dataDir)
    {
        defined('BASE_DIR') || define('BASE_DIR', dirname(__DIR__));
        defined('DATA_DIR') || define('DATA_DIR', $dataDir);
        defined('SRC_DIR') || define('SRC_DIR', BASE_DIR . '/src');
        defined('WEB_DIR') || define('WEB_DIR', BASE_DIR . '/public');
        
        if(file_exists(BASE_DIR . '/.env')) {
            (new Dotenv())->load(BASE_DIR . '/.env');
        }
    }

    public static function initApplication(): App
    {
        Container::setInstance(null);
        $container = Container::getInstance();

        self::registerServices($container);
        self::bootServices($container);
        
        return self::buildApplication($container);
    }

    protected static function registerServices(Container $container)
    {
        $container->singleton(Filesystem::class, function (ContainerInterface $container, $args) {
            return new Filesystem();
        });
        $container->singleton(EventDispatcherInterface::class, function (ContainerInterface $container, $args) {
            return new Dispatcher($container);
        });
        $container->singleton(Configuration::class, function (ContainerInterface $container, $args) {
            $config = new Configuration(DATA_DIR . '/config.json', self::CURRENT_APP_VERSION);
            date_default_timezone_set($config->system->timezone);
            $config->getSystem()->theme = $config->getSystem()->theme ?? 'smoothness';
            return $config;
        });
        $container->singleton(Manager::class, function (ContainerInterface $container, $args) {
            $eventDispatcher = $container->get(EventDispatcherInterface::class);
            assert($eventDispatcher instanceof EventDispatcherInterface);
            $config = $container->get(Configuration::class);
            assert($config instanceof Configuration);
            return Bootstrap::initCapsule($config, $container, $eventDispatcher);
        });
        $container->alias(Manager::class, 'db');
        $container->singleton(MigrationRepositoryInterface::class, function (ContainerInterface $container, $args) {
            $manager = $container->get('db');
            assert($manager instanceof Manager);
            return new DatabaseMigrationRepository($manager->getDatabaseManager(), 'migrations');
        });
        $container->singleton(Migrator::class, function (ContainerInterface $container, $args) {
            $eventDispatcher = $container->get(EventDispatcherInterface::class);
            assert($eventDispatcher instanceof EventDispatcherInterface);
            $manager = $container->get('db');
            assert($manager instanceof Manager);
            $fs = $container->get(Filesystem::class);
            assert($fs instanceof Filesystem);
            $repository = $container->get(MigrationRepositoryInterface::class);
            assert($repository instanceof MigrationRepositoryInterface);
            return new Migrator($repository, $manager->getDatabaseManager(), $fs, $eventDispatcher);
        });
        $container->bind(AppMigrator::class, function (ContainerInterface $container, $args) {
            return new AppMigrator($container->get(Migrator::class));
        });
        $container->singleton(Environment::class, function (ContainerInterface $container, $args) {
            $config = $container->get(Configuration::class);
            assert($config instanceof Configuration);
            return Bootstrap::initTwig($config);
        });
        $container->singleton(Translator::class, function (ContainerInterface $container, $args) {
            $translator = Bootstrap::initTranslator();
            $config = Container::getInstance()->get(Configuration::class);
            $locale = str_replace('-', '_', $config->system->language);
            $translator->setLocale($locale);
            return $translator;
        });
        $container->bind(IlluminateTranslator::class, function (ContainerInterface $container, $args) {
            // TODO: correct validation message
            return new IlluminateTranslator((new ArrayLoader())
                ->addMessages('en-Us', '', [
                    'validation.required' => 'ssssssss'
                ]), 'en-US');
        });
        $container->bind(CoverExtractor::class, function(ContainerInterface $container, $args){
            return new CoverExtractor($container->get(Configuration::class));
        });
    }
    
    
    protected static function bootServices(Container $container)
    {
        $container->get('db');
    }
    
    protected static function buildApplication(Container $container): App
    {
        $app = AppFactory::create(null, $container);
        $app->addBodyParsingMiddleware();
        self::initExceptionHandling($app);
        $app->addRoutingMiddleware();
        Routes::register($app);

        return $app;
    }

    protected static function initExceptionHandling(App $app)
    {
        $logger = self::initAppLogger();
        
        $errorMiddleware = $app->addErrorMiddleware(self::DEBUG_MODE, true, true, $logger);
        $errorHandler = new ErrorHandler($app->getCallableResolver(), $app->getResponseFactory(), $logger);
        $errorHandler->registerErrorRenderer('application/json', JsonErrorRenderer::class);
        $errorMiddleware->setDefaultErrorHandler($errorHandler);

        $shutdownHandler = new ShutdownHandler($errorHandler);
        register_shutdown_function($shutdownHandler);
    }

    protected static function initAppLogger(): Logger
    {
        $logHandler = new RotatingFileHandler(DATA_DIR.'/logs/app.log',5,Logger::DEBUG);
        $logHandler->setFormatter(new LineFormatter(null, null, true, true));
        return new Logger('app', [$logHandler]);
    }

    protected static function initTranslator()
    {
        $translator = new Translator('en_US');
        $translator->setLocale('uk_UA');
        $translator->addLoader('php', new PhpFileLoader());
        $translator->addResource('php', SRC_DIR . '/i18n/uk_UA.php', 'uk_UA');
        return $translator;

    }

    protected static function initTwig(Configuration $config)
    {
        $loader = new FilesystemLoader(SRC_DIR . '/views');
        $twig = new Environment($loader, [
            'debug' => self::DEBUG_MODE,
        ]);
        $twig->addFunction(new TwigFunction('copy_book_dir', function () use ($config) {
            return str_replace("\\", "\\\\", $config->library->directory);
        }));

        return $twig;
    }
}
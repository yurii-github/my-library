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

use App\Handlers\ErrorHandler;
use App\Providers\ApplicationProvider;
use App\Providers\ConfigurationProvider;
use App\Providers\CoverExtractorProvider;
use App\Providers\DatabaseProvider;
use App\Providers\EnvironmentProvider;
use App\Providers\MigratorProvider;
use App\Renderers\JsonErrorRenderer;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Slim\App;
use Slim\Factory\AppFactory;
use \Illuminate\Container\Container;

class Application extends App
{
    public const CURRENT_APP_VERSION = '2.0';
    public const DEBUG_MODE = true;
    public const DISPLAY_ERRORS = self::DEBUG_MODE;

    public function __construct()
    {
        ini_set('display_errors', '1');
        error_reporting(E_ALL);
        $responseFactory = AppFactory::determineResponseFactory();
        $callableResolver = $routeCollector = $routeResolver = $middlewareDispatcher = null;
        Container::setInstance(null);
        $container = Container::getInstance();
        parent::__construct($responseFactory, $container, $callableResolver, $routeCollector, $routeResolver, $middlewareDispatcher);

        $this->registerServices();
        $this->initExceptionHandling();
        $this->addBodyParsingMiddleware();
        $this->addRoutingMiddleware();
        Routes::register($this);
        $this->bootServices();
    }

    public function getContainer(): ?Container
    {
        return parent::getContainer();
    }

    protected function registerServices()
    {
        ApplicationProvider::register($this->getContainer());
        ConfigurationProvider::register($this->getContainer());
        DatabaseProvider::register($this->getContainer());
        MigratorProvider::register($this->getContainer());
        EnvironmentProvider::register($this->getContainer());
        CoverExtractorProvider::register($this->getContainer());
    }

    protected function bootServices()
    {
        DatabaseProvider::boot($this->getContainer());
    }

    protected function initExceptionHandling()
    {
        $logger = $this->initLogger();
        $errorMiddleware = $this->addErrorMiddleware(self::DISPLAY_ERRORS, true, self::DEBUG_MODE, $logger);
        $errorHandler = new ErrorHandler($this->getCallableResolver(), $this->getResponseFactory(), $logger);
        $errorHandler->registerErrorRenderer('application/json', JsonErrorRenderer::class);
        $errorMiddleware->setDefaultErrorHandler($errorHandler);
    }

    protected function initLogger(): Logger
    {
        $logHandler = new RotatingFileHandler(DATA_DIR . '/logs/app.log', 5, Logger::DEBUG);
        $logHandler->setFormatter(new LineFormatter(null, null, true, true));
        return new Logger('app', [$logHandler]);
    }

}
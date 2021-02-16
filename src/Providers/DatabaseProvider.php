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

namespace App\Providers;

use App\Configuration\Configuration;
use Illuminate\Container\Container;
use Illuminate\Contracts\Container\Container as ContainerInterface;
use Illuminate\Contracts\Events\Dispatcher as EventDispatcherInterface;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Migrations\DatabaseMigrationRepository;
use Illuminate\Database\Migrations\MigrationRepositoryInterface;

class DatabaseProvider
{
    public static function boot(Container $container)
    {
        $container->get('db');
    }
    
    public static function register(Container $container)
    {
        $container->singleton(Manager::class, function (Container $container, $args) {
            return self::initCapsule($container);
        });
        $container->bind(MigrationRepositoryInterface::class, function (ContainerInterface $container, $args) {
            $manager = $container->get(Manager::class);
            assert($manager instanceof Manager);
            return new DatabaseMigrationRepository($manager->getDatabaseManager(), 'migrations');
        });
        $container->alias(Manager::class, 'db');
    }
    
    protected static function initCapsule(Container $container): Manager
    {
        $eventDispatcher = $container->get(EventDispatcherInterface::class);
        assert($eventDispatcher instanceof EventDispatcherInterface);
        $config = $container->get(Configuration::class);
        assert($config instanceof Configuration);
        
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
}
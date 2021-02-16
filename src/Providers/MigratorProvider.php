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

use App\AppMigrator;
use Illuminate\Contracts\Container\Container as ContainerInterface;
use Illuminate\Contracts\Events\Dispatcher as EventDispatcherInterface;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Migrations\MigrationRepositoryInterface;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Filesystem\Filesystem;

class MigratorProvider
{
    public static function register(ContainerInterface $container)
    {
        $container->bind(Migrator::class, function (ContainerInterface $container, $args) {
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
    }
    
}
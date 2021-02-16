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

use App\Application;
use App\Configuration\Configuration;
use Illuminate\Contracts\Container\Container as ContainerInterface;

class ConfigurationProvider
{
    public static function register(ContainerInterface $container)
    {
        $container->singleton(Configuration::class, function (ContainerInterface $container, $args) {
            $config = new Configuration(DATA_DIR . '/config.json', Application::CURRENT_APP_VERSION);
            date_default_timezone_set($config->system->timezone);
            $config->getSystem()->theme = $config->getSystem()->theme ?? 'smoothness';
            return $config;
        });
    }
}
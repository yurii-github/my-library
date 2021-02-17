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
use Illuminate\Container\Container;
use Illuminate\Contracts\Container\Container as ContainerInterface;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Translation\FileLoader;
use Illuminate\Translation\Translator;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;

class EnvironmentProvider implements ProviderInterface
{
    public function register(ContainerInterface $container)
    {
        $container->bind(Environment::class, function (ContainerInterface $container, $args) {
            $config = $container->get(Configuration::class);
            assert($config instanceof Configuration);
            $loader = new FilesystemLoader(BASE_DIR . '/src/views');
            $twig = new Environment($loader, [
                'debug' => Application::DEBUG_MODE,
            ]);
            $twig->addFunction(new TwigFunction('copy_book_dir', function () use ($config) {
                return str_replace("\\", "\\\\", $config->library->directory);
            }));

            return $twig;
        });

        $container->bind(Translator::class, function (ContainerInterface $container, $args) {
            $config = Container::getInstance()->get(Configuration::class);
            assert($config instanceof Configuration);
            $locale = $config->system->language;
            return new Translator(new FileLoader($container->get(Filesystem::class), BASE_DIR .'/src/i18n'), $locale);
        });
    }
    
    public function boot(ContainerInterface $container)
    {
    }
}
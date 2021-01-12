<?php
/*
 * My Book Library
 *
 * Copyright (C) 2014-2020 Yurii K.
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

namespace App\Actions\Pages;

use App\Configuration\Configuration;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Translation\Translator;
use Twig\Environment;
use \App\Models\Category;

class GetConfigIndexAction
{
    /**
     * @var Configuration
     */
    protected $config;
    protected $twig;
    protected $translator;


    public function __construct(ContainerInterface $container)
    {
        $this->config = $container->get(Configuration::class);
        $this->twig = $container->get(Environment::class);
        $this->translator = $container->get(Translator::class);
    }


    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $uri = $request->getUri();
        $gridLocale = [
            'en_US' => 'en',
            'uk_UA' => 'ua',
        ];
        
        $c = 1;
        $categories = Category::all();
        $params = [
            't' => $this->translator,
            'categories' => $categories,
            'path' => $uri->getPath(),
            'baseUrl' => $uri->getScheme() . '://' . $uri->getAuthority(),
            'url' => $uri->getScheme() . '://' . $uri->getAuthority() . $uri->getPath(),
            'appTheme' => $this->config->getSystem()->theme,
            'gridLocale' => $gridLocale[$this->translator->getLocale()],

            'PHP_VERSION' => PHP_VERSION,
            'SUPPORTED_VALUES' => $this->config::SUPPORTED_VALUES,
            'SUPPORTED_DATABASES' => ['sqlite' => 'SQLite','mysql' => 'MySQL'],
            'config' => $this->config,
            'INTL_ICU_VERSION' => INTL_ICU_VERSION,
            'timeZones' => \DateTimeZone::listIdentifiers()
        ];
        $response->getBody()->write($this->twig->render('config.html.twig', $params));

        return $response;
    }
}
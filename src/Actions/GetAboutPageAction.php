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

namespace App\Actions;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Translation\Translator;
use Twig\Environment;


class GetAboutPageAction
{
    protected $twig;
    protected $translator;


    public function __construct(ContainerInterface $container)
    {
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
        $response->getBody()->write($this->twig->render('about.html.twig', [
            't' => $this->translator,
            'path' => $uri->getPath(),
            'baseUrl' => $uri->getScheme() . '://' . $uri->getAuthority(),
            'appTheme' => $_ENV['APP_THEME'],
            'gridLocale' => $gridLocale[$this->translator->getLocale()],
            'projects' => [
                'Slim 4' => 'https://www.slimframework.com/',
                'jQuery' => 'https://jquery.com',
                'jQuery UI' => 'https://jqueryui.com',
                'jQuery Grid' => 'http://www.trirand.com/blog',
                'jQuery Raty' => 'http://wbotelhos.com/raty',
                'jQuery FancyBox' => 'http://fancybox.net',
                'JS-Cookie' => 'https://github.com/js-cookie/js-cookie',
                'Ghostscript' => 'https://www.ghostscript.com/'
            ]
        ]));

        return $response;
    }
}
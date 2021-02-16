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

namespace App\Actions;

use App\Configuration\Configuration;
use GuzzleHttp\Psr7\Response;
use Illuminate\Translation\Translator;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Twig\Environment;

abstract class AbstractPageAction
{
    /** @var Configuration */
    protected $config;
    /** @var Environment */
    protected $twig;
    /** @var Translator */
    protected $translator;

    abstract public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface;

    public function __construct(ContainerInterface $container)
    {
        $this->config = $container->get(Configuration::class);
        $this->twig = $container->get(Environment::class);
        $this->translator = $container->get(Translator::class);
    }

    protected function asPage(ServerRequestInterface $request, string $view, array $data): Response
    {
        $body = $this->render($request, $view, $data);
        return new Response(200, ['Content-Type' => ['text/html']], $body);
    }

    protected function render(ServerRequestInterface $request, string $view, array $data): string
    {
        $uri = $request->getUri();
        $gridLocale = [
            'en-US' => 'en',
            'uk-UA' => 'ua',
        ];
        $baseData = [
            't' => $this->translator,
            'path' => $uri->getPath(),
            'baseUrl' => $baseUrl = $uri->getScheme() . '://' . $uri->getAuthority(),
            'appTheme' => $this->config->getSystem()->theme,
            'gridLocale' => $gridLocale[$this->translator->getLocale()],
            'config' => $this->config,
            'currentUrl' => $baseUrl . $uri->getPath(),
            'APP_VERSION' => 'v.' . $this->config->getVersion(),
            'APP_LANGUAGE' => $this->config->getSystem()->language,
        ];
        $data = array_merge($baseData, $data);

        return $this->twig->render($view, $data);
    }

}
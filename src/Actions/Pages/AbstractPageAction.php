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
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Translation\Translator;
use Twig\Environment;


abstract class AbstractPageAction
{
    /** @var Configuration */
    protected $config;
    /** @var Environment */
    protected $twig;
    protected $translator;


    public function __construct(ContainerInterface $container)
    {
        $this->config = $container->get(Configuration::class);
        $this->twig = $container->get(Environment::class);
        $this->translator = $container->get(Translator::class);
    }

    
    protected function render(ServerRequestInterface $request, string $view, array $data)
    {
        $uri = $request->getUri();
        $gridLocale = [
            'en_US' => 'en',
            'uk_UA' => 'ua',
        ];
        $baseData = [
            't' => $this->translator,
            'path' => $uri->getPath(),
            'baseUrl' => $uri->getScheme() . '://' . $uri->getAuthority(),
            'appTheme' => $this->config->getSystem()->theme,
            'gridLocale' => $gridLocale[$this->translator->getLocale()],
            'config' => $this->config,
            'currentUrl' => $uri->getScheme() . '://' . $uri->getAuthority() . $uri->getPath(),
            'APP_VERSION' => 'v.'.$this->config->getVersion(),
            'APP_LANGUAGE' => $this->config->getSystem()->language,
        ];
        $data = array_merge($baseData, $data);
        
        return $this->twig->render($view, $data);
    }

}
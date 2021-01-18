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

use App\Helpers\Tools;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Twig\Environment;

class GetPhpInfoAction
{
    /** @var Environment */
    protected $twig;

    public function __construct(ContainerInterface $container)
    {
        $this->twig = $container->get(Environment::class);
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $html = $this->twig->render('phpinfo.html.twig', ['phpInfo' => Tools::getPhpInfo()]);
        $response->getBody()->write($html);

        return $response;
    }
}
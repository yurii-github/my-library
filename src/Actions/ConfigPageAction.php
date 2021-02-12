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

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use \App\Models\Category;
use \DateTimeZone;

class ConfigPageAction extends AbstractPageAction
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        return $this->asPage($request, 'config.html.twig', [
            'PHP_VERSION' => PHP_VERSION,
            'SUPPORTED_VALUES' => $this->config::SUPPORTED_VALUES,
            'SUPPORTED_DATABASES' => [
                'sqlite' => 'SQLite',
                'mysql' => 'MySQL'
            ],
            'INTL_ICU_VERSION' => INTL_ICU_VERSION,
            'timeZones' => DateTimeZone::listIdentifiers(),
            'categories' => Category::all(),
        ]);
    }
}
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

use App\AppMigrator;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use \App\Models\Category;

class IndexPageAction extends AbstractPageAction
{
    /** @var AppMigrator */
    protected $migrator;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->migrator = $container->get(AppMigrator::class);
        assert($this->migrator instanceof AppMigrator);
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $this->migrator->migrate();

        return $this->asPage($request, 'index.html.twig', [
            'categories' => Category::all(),
        ]);
    }
}
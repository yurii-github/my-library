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

use Illuminate\Database\Migrations\Migrator;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Console\Output\StreamOutput;

class MigrateDatabaseAction
{
    /** @var Migrator */
    protected $migrator;

    
    public function __construct(ContainerInterface $container)
    {
        $this->migrator = $container->get(Migrator::class);
    }


    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        if (!$this->migrator->repositoryExists()) {
            $this->migrator->getRepository()->createRepository();
        }

        $output = new StreamOutput(fopen('php://temp', 'r+'));
        $this->migrator->setOutput($output);
        $this->migrator->run(SRC_DIR.'/migrations', [
            'pretend' => false,
            'step' => true,
        ]);

        rewind($output->getStream());
        $output = stream_get_contents($output->getStream());
        $response->getBody()->write(implode("<br>", explode("\n", $output)));

        return $response;
    }
    
}
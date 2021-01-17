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

use App\Configuration\Configuration;
use App\Models\Book;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ConfigCheckFilesAction
{
    /**
     * @var Configuration
     */
    protected $config;


    public function __construct(ContainerInterface $container)
    {
        $this->config = $container->get(Configuration::class);
    }


    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        // TODO: read with iterator, not all. may use too much memory
        $files_db = [];
        foreach (Book::query()->select(['filename'])->get()->all() as $book) {
            $files_db[] = $book['filename'];
        }

        $files = $this->config->getLibraryBookFilenames();
        $arr_db_only = array_diff($files_db, $files);
        $arr_fs_only = array_diff($files, $files_db);

        $response->getBody()->write(json_encode([
            'db' => array_values($arr_db_only),
            'fs' => array_values($arr_fs_only)
        ], JSON_UNESCAPED_UNICODE));

        return $response;
    }


}
<?php declare(strict_types=1);
/*
 * My Book Library
 *
 * Copyright (C) 2014-2024 Yurii K.
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

namespace App\Actions\Api\Config;

use App\Actions\AbstractApiAction;
use App\Configuration\Configuration;
use App\Models\Book;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class CheckFilesAction extends AbstractApiAction
{
    protected Configuration $config;

    public function __construct(ContainerInterface $container)
    {
        $this->config = $container->get(Configuration::class);
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $files_db = Book::query()->select(['filename'])->get()->transform(function (Book $book) {
            return $book->file->getFilename();
        })->all();

        $files = $this->config->getLibraryBookFilenames();
        $arr_db_only = array_diff($files_db, $files);
        $arr_fs_only = array_diff($files, $files_db);

        $data = [
            'db' => array_values($arr_db_only),
            'fs' => array_values($arr_fs_only)
        ];

        return $this->asJSON($data);
    }

}

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

/**
 * Imports book cover from book if it is PDF
 * Basically, get image from its 1st page
 */
class ConfigGetImportNewCoverFromPdfAction
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
        $books = Book::query()->select(['filename', 'book_guid'])
            ->whereRaw('book_cover IS NULL')
            ->whereRaw("filename LIKE '%.pdf'")
            ->get()->toArray();

        $response->getBody()->write(json_encode($books, JSON_UNESCAPED_UNICODE));

        return $response;
    }
}
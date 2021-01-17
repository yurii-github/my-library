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
use \App\Models\Book;

class GetBookCoverAction
{
    public function __construct(ContainerInterface $container)
    {
    }


    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $book_guid = $request->getQueryParams()['book_guid'];
        $cover = Book::query()->where('book_guid', $book_guid)->first('book_cover')->book_cover;
        $response = $response
            ->withHeader('Cache-Control', 'no-cache')
            ->withHeader('Content-Type', 'image/jpeg');
        $response->getBody()->write(!is_null($cover) ? $cover : file_get_contents(WEB_DIR.'/assets/app/book-cover-empty.jpg'));
        return $response;
    }
}
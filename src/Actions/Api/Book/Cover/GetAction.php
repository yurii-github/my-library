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

namespace App\Actions\Api\Book\Cover;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use \App\Models\Book;

class GetAction
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $cover = Book::select('book_cover')->where('book_guid', $args['book_guid'])->first('book_cover')->book_cover;
        $response = $response
            ->withHeader('Cache-Control', 'no-store')
            ->withHeader('Content-Type', 'image/jpeg');
        $response->getBody()->write(!is_null($cover) ? $cover : file_get_contents(WEB_DIR.'/assets/app/book-cover-empty.jpg'));
        return $response;
    }
}
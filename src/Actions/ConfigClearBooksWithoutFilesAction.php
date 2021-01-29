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

use App\Models\Book;
use Illuminate\Support\Arr;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ConfigClearBooksWithoutFilesAction extends AbstractApiAction
{
    
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $post = $request->getParsedBody();
        $stepping = Arr::get($post, 'stepping', 5);

        $deletedBooks = Book::query()->select(['book_guid', 'filename'])->limit($stepping)->get()
            ->filter(function (Book $book) {
                return !$book->file_exists;
            })->each(function (Book $book) {
                $book->delete();
            })->modelKeys();

        return $this->asJSON($response, $deletedBooks);
    }

}
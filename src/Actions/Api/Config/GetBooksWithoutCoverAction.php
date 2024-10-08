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
use App\Models\Book;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class GetBooksWithoutCoverAction extends AbstractApiAction
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $type = 'pdf';
        $books = Book::query()->select(['filename', 'book_guid'])
            ->whereRaw('book_cover IS NULL')
            ->where('filename', 'like', '%'.$type)
            ->get()
            ->toArray();

        return $this->asJSON($books);
    }
}

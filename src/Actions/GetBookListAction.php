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
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Translation\Translator;
use Twig\Environment;
use \App\Models\Category;
use \App\Models\Book;
use \App\JGridRequestQuery;
use \Illuminate\Database\Eloquent\Builder;


class GetBookListAction
{
    public function __construct(ContainerInterface $container)
    {
    }


    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $params = $request->getQueryParams();
        $filterCategories = $params['filterCategories'] ?? null;
        $columns = ['created_date', 'book_guid', 'favorite', 'read', 'year', 'title', 'isbn13', 'author', 'publisher', 'ext', 'filename'];
        $query = Book::query()->select($columns);

        if (!empty($filterCategories)) {
            $query->whereHas('categories', function (Builder $query) use ($filterCategories) {
                $query->whereIn('guid', explode(',', $filterCategories));
            });
        }

        $gridQuery = new JGridRequestQuery($query, $request);
        $gridQuery->withFilters()->withSorting('created_date', 'desc');;
        $response->getBody()->write(json_encode($gridQuery->paginate($columns)));
        
        return $response->withHeader('Content-Type', 'application/json');
    }
}
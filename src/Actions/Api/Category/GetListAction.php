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

namespace App\Actions\Api\Category;

use App\Actions\AbstractApiAction;
use App\JGridRequestQuery;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Arr;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use \App\Models\Category;

class GetListAction extends AbstractApiAction
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $bookId = Arr::get($request->getQueryParams(), 'nodeid');
        $bookId = $bookId ? (string)$bookId : null;
        $query = Category::query()
            ->select([
                'guid',
                'title',
                new Expression('CASE WHEN bc.category_guid IS NOT NULL THEN 1 ELSE 0 END AS marker')
            ])
            ->leftJoin('books_categories AS bc', function (Builder $query) use ($bookId) {
                return $query
                    ->whereRaw('bc.category_guid = categories.guid')
                    ->where('bc.book_guid', '=', $bookId);
            });

        $gridQuery = new JGridRequestQuery($query, $request);
        $gridQuery->withFilters()->withSorting('title', 'asc');;

        return $this->asJSON($gridQuery->paginate(['guid', 'title', 'marker']));
    }
}
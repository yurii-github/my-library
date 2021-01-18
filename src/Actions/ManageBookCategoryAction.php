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
use \App\Models\Category;

class ManageBookCategoryAction
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $post = $request->getParsedBody();
        $operation = Arr::get($post, 'oper');
        $bookId = Arr::get($request->getQueryParams(), 'nodeid');
        $categoryId = Arr::get($post, 'id');
        $categoryTitle = Arr::get($post, 'title');
        $bookMarker = (bool)Arr::get($post, 'marker', false);

        if ($operation === 'add') {
            $category = new Category();
            $category->title = $categoryTitle;
            $category->saveOrFail();
            return $response;
        } elseif ($operation === 'del') {
            Category::destroy($categoryId);
            return $response;
        } elseif ($operation === 'edit') {
            $category = Category::query()->findOrFail(Arr::get($post, 'id'));
            assert($category instanceof Category);
            $book = Book::query()->find($bookId);
            if ($book) {
                assert($book instanceof Book);
                if ($bookMarker) {
                    $book->categories()->attach($category);
                } else {
                    $book->categories()->detach($category);
                }
            }
            if ($title = Arr::get($post, 'title')) {
                $category->title = Arr::get($post, 'title');
                $category->saveOrFail();
            }
            return $response;
        }

        throw new \Exception('Unsupported operation!');
    }

}
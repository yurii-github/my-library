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

namespace App\Actions\Api\Book\Category;

use App\Actions\AbstractApiAction;
use App\Actions\WithValidateTrait;
use App\Exception\UnsupportedOperationException;
use App\Models\Book;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Support\Arr;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Rule;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use \App\Models\Category;

class ManageAction extends AbstractApiAction
{
    use WithValidateTrait;
    
    /** @var Translator */
    protected $translator;
    /** @var Manager */
    protected $db;

    public function __construct(ContainerInterface $container)
    {
        $this->db = $container->get('db');
        assert($this->db instanceof Manager);
        $this->translator = $container->get(Translator::class);
        assert($this->translator instanceof Translator);
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $bookId = Arr::get($args, 'book_id');
        $post = $request->getParsedBody();
        $operation = Arr::get($post, 'oper');

        if ($operation === 'add') {
            $this->addCategory($post);
            return $this->asJSON();
        } elseif ($operation === 'del') {
            $this->deleteCategory($post);
            return $response;
        } elseif ($operation === 'edit') {
            if ($bookId) {
                $post = Arr::add($post, 'book_id', $bookId);
            }
            $category = $this->editCategory($post);
            return $this->asJSON($category);
        }

        throw new UnsupportedOperationException($operation);
    }

    protected function editCategory(array $post): ?Category
    {
        $input = $this->validate($post, [
            'id' => ['required', 'string', 'max:255', Rule::exists('categories', 'guid')],
            'title' => ['sometimes', 'string', 'max:255'],
            'book_id' => ['sometimes', 'string', 'max:255', Rule::exists('books', 'book_guid')],
            'marker' => ['required_with:book_id', 'bool']
        ]);

        $category = Category::find($input['id']);
        assert($category instanceof Category);

        if (Arr::has($input, 'title')) {
            $category->title = $input['title'];
            $category->saveOrFail();
        }

        if (Arr::has($input, 'book_id')) {
            $book = Book::whereKey($input['book_id'])->sole();
            assert($book instanceof Book);
            if ($input['marker']) {
                $book->categories()->attach($category);
            } else {
                $book->categories()->detach($category);
            }
        }

        return $category;
    }


    protected function deleteCategory(array $post): ?Category
    {
        $input = $this->validate($post, [
            'id' => ['required', 'string', 'max:255'],
        ]);

        $category = Category::find($input['id']);
        assert($category instanceof Category || $category === null);

        if ($category) {
            $category->delete();
        }

        return $category;
    }

    protected function addCategory(array $post): Category
    {
        $input = $this->validate($post, [
            'title' => ['required'],
        ]);

        $category = new Category();
        $category->title = $input['title'];
        $category->saveOrFail();

        return $category;
    }
}
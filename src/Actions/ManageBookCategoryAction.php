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
use Illuminate\Database\Capsule\Manager;
use Illuminate\Support\Arr;
use Illuminate\Translation\Translator;
use Illuminate\Validation\DatabasePresenceVerifier;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use \App\Models\Category;

class ManageBookCategoryAction extends AbstractApiAction
{
    /** @var Translator */
    protected $translator;
    /** @var Manager */
    protected $db;

    public function __construct(ContainerInterface $container)
    {
        $this->db = $container->get('db');
        assert($this->db instanceof Manager);
        $this->translator = $container->get(Translator::class);
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        //TODO: why asJSON fails? some format?
        $post = $request->getParsedBody();
        $operation = Arr::get($post, 'oper');

        try {
            if ($operation === 'add') {
                $this->addCategory($post);
                $response = $this->asJSON($response);
                return $response;
            } elseif ($operation === 'del') {
                $this->deleteCategory($post);
                return $response;
            } elseif ($operation === 'edit') {
                if ($nodeid = Arr::get($request->getQueryParams(), 'nodeid')) {
                    $post = Arr::add($post, 'nodeid', $nodeid);
                }
                $this->editCategory($post);
                return $response;
            }
            throw new \Exception('Unsupported operation!');
        } catch (ValidationException $e) {
            $response = $this->asJSON($response, $e->errors());
            return $response->withStatus(422);
        } catch (\Throwable $e) {
            $response = $this->asJSON($response, ['error' => $e->getMessage()]);
            return $response->withStatus(400);
        }
    }

    protected function editCategory(array $post): ?Category
    {
        $rules = [
            'id' => ['required', 'string', 'max:255', Rule::exists('categories', 'guid')],
            'title' => ['sometimes', 'string', 'max:255'],
            'nodeid' => ['sometimes', 'string', 'max:255', Rule::exists('books', 'book_guid')],
            'marker' => ['required_with:nodeid', 'bool']
        ];

        $validator = new Validator($this->translator, $post, $rules);
        $validator->setPresenceVerifier(new DatabasePresenceVerifier($this->db->getDatabaseManager()));
        $input = $validator->validate();

        $category = Category::find($input['id']);
        assert($category instanceof Category);

        if (Arr::has($input, 'title')) {
            $category->title = $input['title'];
            $category->saveOrFail();
        }

        if (Arr::has($input, 'nodeid')) {
            $book = Book::find($input['nodeid']);
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
        $rules = [
            'id' => ['required', 'string', 'max:255'],
        ];

        $input = (new Validator($this->translator, $post, $rules))->validate();

        $category = Category::find($input['id']);
        assert($category instanceof Category || $category === null);

        if ($category) {
            $category->delete();
        }

        return $category;
    }

    protected function addCategory(array $post): Category
    {
        $rules = [
            'title' => ['required'],
        ];

        $input = (new Validator($this->translator, $post, $rules))->validate();

        $category = new Category();
        $category->title = $input['title'];
        $category->saveOrFail();

        return $category;
    }
}
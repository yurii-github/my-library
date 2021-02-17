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

namespace App\Actions\Api\Book;

use App\Actions\AbstractApiAction;
use App\Actions\WithValidateTrait;
use App\Exception\UnsupportedOperationException;
use App\Models\Book;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use \Illuminate\Translation\Translator;

class ManageAction extends AbstractApiAction
{
    use WithValidateTrait;

    protected Translator $translator;
    protected Manager $db;

    public function __construct(ContainerInterface $container)
    {
        $this->db = $container->get('db');
        $this->translator = $container->get(Translator::class);
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $post = $request->getParsedBody();
        $operation = Arr::get($post, 'oper');

        if ($operation === 'add') {
            $book = $this->addBook($post);
            return $this->asJSON($book);
        } elseif ($operation === 'del') {
            $this->deleteBook($post);
            return $this->asJSON();
        } elseif ($operation === 'edit') {
            $book = $this->editBook($post);
            return $this->asJSON($book);
        }

        throw new UnsupportedOperationException($operation);
    }

    protected function editBook(array $post): ?Book
    {
        $input = $this->validate($post, [
            'year' => ['sometimes'],
            'favorite' => ['sometimes', 'required'],
            'read' => ['sometimes', 'required', Rule::in(['yes', 'no'])],
            'title' => ['sometimes', 'required'],
            'isbn13' => ['sometimes', 'sometimes'],
            'author' => ['sometimes', 'string'],
            'publisher' => ['sometimes', 'string'],
            'ext' => ['sometimes'],
            'id' => ['required', 'exists:books,book_guid']
        ]);

        $book = Book::findOrFail($input['id']);
        assert($book instanceof Book);
        $book->fill($input);
        $book->save();

        return $book;
    }

    protected function deleteBook(array $post): ?Book
    {
        $input = $this->validate($post, [
            'id' => ['required', 'string']
        ]);

        $book = Book::find($input['id']);
        assert($book instanceof Book || $book === null);
        if ($book) {
            $book->delete();
        }

        return $book;
    }

    protected function addBook(array $post): Book
    {
        $input = $this->validate($post, [
            'year' => ['sometimes'],
            'favorite' => ['required'],
            'read' => ['required', Rule::in(['yes', 'no'])],
            'title' => ['required'],
            'isbn13' => ['sometimes'],
            'author' => ['sometimes'],
            'publisher' => ['sometimes'],
            'ext' => ['sometimes'],
        ]);

        $book = new Book();
        $book->fill($input);
        $book->save();

        return $book;
    }

}
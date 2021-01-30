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
use Illuminate\Validation\DatabasePresenceVerifier;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use \Illuminate\Translation\Translator;

class ManageBookAction extends AbstractApiAction
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
        assert($this->translator instanceof Translator);
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $post = $request->getParsedBody();
        $operation = Arr::get($post, 'oper');

        try {
            if ($operation === 'add') {
                $book = $this->addBook($post);
                $response->getBody()->write(json_encode($book->toArray(), JSON_UNESCAPED_UNICODE));
                return $response;
            } elseif ($operation === 'del') {
                $this->deleteBook($post);
                return $response;
            } elseif ($operation === 'edit') {
                $book = $this->editBook($post);
                return $response;
            }
            throw new \Exception('Unsupported operation!');
        } catch (ValidationException $e) {
            $response = $this->asJSON($response,$e->errors());
            return $response->withStatus(422);
        } catch (\Throwable $e) {
            $response = $this->asJSON($response, ['error' => $e->getMessage()]);
            return $response->withStatus(400);
        }
    }

    protected function editBook(array $post): ?Book
    {
        $rules = [
            'year' => ['sometimes'],
            'favorite' => ['sometimes', 'required'],
            'read' => ['sometimes', 'required', Rule::in(['yes', 'no'])],
            'title' => ['sometimes', 'required'],
            'isbn13' => ['sometimes', 'sometimes'],
            'author' => ['sometimes', 'string'],
            'publisher' => ['sometimes', 'string'],
            'ext' => ['sometimes'],
            'id' => ['required', 'exists:books,book_guid']
        ];
        $validator = new Validator($this->translator, $post, $rules);
        $validator->setPresenceVerifier(new DatabasePresenceVerifier($this->db->getDatabaseManager()));
        $input = $validator->validate();
        $book = Book::findOrFail($input['id']);
        assert($book instanceof Book);
        $book->fill($input);
        $book->save();

        return $book;
    }

    protected function deleteBook(array $post): ?Book
    {
        $rules = [
            'id' => ['required', 'string']
        ];
        $input = (new Validator($this->translator, $post, $rules))->validate();
        $book = Book::find($input['id']);
        assert($book instanceof Book || $book === null);
        if ($book) {
            $book->delete();
        }

        return $book;
    }

    protected function addBook(array $post): Book
    {
        $rules = [
            'year' => ['sometimes'],
            'favorite' => ['required'],
            'read' => ['required', Rule::in(['yes', 'no'])],
            'title' => ['required'],
            'isbn13' => ['sometimes'],
            'author' => ['sometimes'],
            'publisher' => ['sometimes'],
            'ext' => ['sometimes'],
        ];
        $validator = new Validator($this->translator, $post, $rules);
        $input = $validator->validate();
        $book = new Book();
        $book->fill($input);
        $book->save();

        return $book;
    }

}
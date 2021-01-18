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

use App\Exception\BookFileNotFoundException;
use App\Models\Book;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use \Illuminate\Translation\Translator;

/**
 * CRUD functionality for books via jqGrid interface
 */
class ManageBookAction
{
    /** @var Translator */
    protected $translator;


    public function __construct(ContainerInterface $container)
    {
        $this->translator = $container->get(Translator::class);
    }


    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $post = $request->getParsedBody();

        switch ($post['oper']) {

            case 'add':
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
                try {
                    $input = $validator->validate();
                    $book = new Book();
                    $book->fill($input);
                    $book->save();
                } catch (BookFileNotFoundException $e) {
                    $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
                    return $response->withStatus(400);
                } catch (ValidationException $e) {
                    $response->getBody()->write(json_encode($e->errors()));
                    return $response->withStatus(422);
                }
                $response->getBody()->write(json_encode($book->toArray(), JSON_UNESCAPED_UNICODE));
                break;

            case 'del':
                Book::where(['book_guid' => $post['id']])->delete();
                break;

            case 'edit':
                $rules = [
                    'year' => ['sometimes'],
                    'favorite' => ['sometimes', 'required'],
                    'read' => ['sometimes', 'required', Rule::in(['yes', 'no'])],
                    'title' => ['sometimes', 'required'],
                    'isbn13' => ['sometimes', 'sometimes'],
                    'author' => ['sometimes', 'string'],
                    'publisher' => ['sometimes', 'string'],
                    'ext' => ['sometimes'],
                ];
                $validator = new Validator($this->translator, $post, $rules);
                $book = Book::where(['book_guid' => $post['id']])->firstOrFail(); // TODO: do not select book cover, add book cover class
                try {
                    $input = $validator->validate();
                    $book->fill($input);
                    $book->save();
                } catch (BookFileNotFoundException $e) {
                    $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
                    return $response->withStatus(400);
                } catch (ValidationException $e) {
                    $response->getBody()->write(json_encode($e->errors()));
                    return $response->withStatus(422);
                }
                break;
        }

        return $response;
    }

}
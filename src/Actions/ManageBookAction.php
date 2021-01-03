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
use App\Models\Book;
use Illuminate\Translation\ArrayLoader;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Translation\Translator;
use Twig\Environment;

/**
 * CRUD functionality for books via jqGrid interface
 */
class ManageBookAction
{
    protected $config;
    protected $twig;
    protected $translator;


    public function __construct(ContainerInterface $container)
    {
        $this->config = $container->get(Configuration::class);
        $this->twig = $container->get(Environment::class);
        $this->translator = $container->get(Translator::class);
    }

    
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $post = $request->getParsedBody();
        
        $t = new \Illuminate\Translation\Translator(new ArrayLoader(), 'en-US');
        $rules = [
            'year' => ['sometimes'],
            'favorite' => ['required'],
            'read' => ['required'], // yes, no
            'title' => ['required'],
            'isbn13' => ['sometimes'],
            'author' => ['sometimes'],
            'publisher' => ['sometimes'],
            'ext' => ['sometimes'],
        ];

        $validator = new Validator($t, $post, $rules);
        
        switch ($post['oper']) {
            
            case 'add':
                try {
                    $input = $validator->validate();
                } catch (ValidationException $e) {
                    $response->getBody()->write(json_encode($e->errors()));
                    return $response->withStatus(422);
                }
                $book = new Book();
                $book->fill($input);
                $book->save();
                break;

            case 'del':
                Book::where(['book_guid' => $post['id']])->delete();
                break;

            case 'edit':
                $book = Book::where(['book_guid' => $post['id']])->firstOrFail(); // TODO: do not select book cover, add book cover class
                try {
                    $input = $validator->validate();
                } catch (ValidationException $e) {
                    $response->getBody()->write(json_encode($e->errors()));
                    return $response->withStatus(422);
                }
                $book->fill($input);
                $book->save();
                break;
        }
        
        return $response;
    }
    
}
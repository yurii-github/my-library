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
use App\Helpers\Tools;
use App\Models\Book;
use app\models\Books;
use Illuminate\Translation\ArrayLoader;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Translation\Translator;
use Twig\Environment;
use \App\Models\Category;

/**
 * saves cover for book via book_guid. cover is sent as request body
 */
class UpdateBookCoverAction
{
    /**
     * @var Configuration
     */
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
        $params = $request->getQueryParams();
        $book = Book::where(['book_guid' => $params['book_guid'] ?? null])->firstOrFail();
        $bookCover = Tools::getResampledImageByWidthAsBlob($request->getBody()->getContents(), $this->config->book->covermaxwidth);
        $book->book_cover = $bookCover;
        $book->save();
        
        return  $response;
    }
}
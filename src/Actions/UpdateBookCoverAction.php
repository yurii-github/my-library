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

use App\Configuration\Configuration;
use App\Exception\InvalidImageException;
use App\Helpers\Tools;
use App\Models\Book;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class UpdateBookCoverAction
{
    /** @var Configuration */
    protected $config;


    public function __construct(ContainerInterface $container)
    {
        $this->config = $container->get(Configuration::class);
    }


    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $params = $request->getQueryParams();
        
        try {
            $book = Book::where(['book_guid' => $params['book_guid'] ?? null])->firstOrFail();
            $bookCover = Tools::getResampledImageByWidthAsBlob($request->getBody()->getContents(), $this->config->book->covermaxwidth);
            $book->book_cover = $bookCover;
            $book->save();
        } catch (InvalidImageException $e) {
            $response->getBody()->write(json_encode(['cover' => 'invalid image'])); // TODO: better format
            return $response->withStatus(422);
        }

        return $response;
    }
}
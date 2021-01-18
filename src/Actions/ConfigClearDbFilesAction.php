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
use App\Models\Book;
use Illuminate\Support\Arr;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ConfigClearDbFilesAction
{
    /**
     * @var Configuration
     */
    protected $config;


    public function __construct(ContainerInterface $container)
    {
        $this->config = $container->get(Configuration::class);
    }


    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $params = $request->getQueryParams();
        
        if (Arr::get($params, 'count') === 'all') {
            $response->getBody()->write($this->countFilesToClear());
            return $response;
        }

        $stepping = Arr::get($params, 'stepping', 5); //records to delete in 1 wave
        $data = [];
        Book::query()->select(['book_guid', 'filename'])->limit($stepping)->get()->each(function (Book $book) use (&$data) {
            if (!$book->fileExists()) {
                $data[] = $book->book_guid;
                $book->delete();
            }
        });

        $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE));

        return $response;
    }


    protected function countFilesToClear()
    {
        $counter = 0;
        Book::query()->select(['book_guid', 'filename'])->each(function (Book $book) use (&$counter) {
            if (!$book->fileExists()) {
                $counter++;
            }
        });

        return $counter;
    }

}
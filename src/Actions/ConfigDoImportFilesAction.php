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
use App\Models\BookFile;
use Illuminate\Support\Arr;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ConfigDoImportFilesAction extends AbstractApiAction
{
    
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $addFilenames = Arr::get($request->getParsedBody(), 'post', []);
        $arr_added = [];
        $message = null;
        try {
            foreach ($addFilenames as $filename) {
                $book = new Book();
                $book->title = $filename;
                $book->file = new BookFile($filename);
                if(!$book->file->exists) {
                    throw new BookFileNotFoundException('Book file does not exist!');
                }
                $book->saveOrFail();
                $arr_added[] = $book->file->filename;
            }
            $message = ['data' => $arr_added, 'result' => true, 'error' => ''];
        } catch (\Throwable $e) {
            $message = ['data' => $arr_added, 'result' => false, 'error' => $e->getMessage()];
        }

        return $this->asJSON($message);
    }

}
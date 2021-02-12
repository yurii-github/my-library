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

namespace App;

use Slim\App;

class Routes
{
    static public function register(App $app)
    {
        $app->get('/', Actions\Pages\IndexPageAction::class);
        $app->get('/api/book/cover', Actions\Api\Cover\GetAction::class);
        $app->get('/api/book', Actions\Api\Book\GetListAction::class);
        $app->post('/api/book/manage', Actions\Api\Book\ManageAction::class);
        $app->post('/api/book/cover-save', Actions\Api\Cover\UpdateAction::class);
        $app->get('/api/category', Actions\Api\Category\GetListAction::class);
        $app->post('/api/category/manage', Actions\Api\Category\ManageAction::class);
        $app->get('/about', Actions\Pages\AboutPageAction::class);
        $app->get('/config', Actions\Pages\ConfigPageAction::class);
        $app->post('/config/save', Actions\Api\Config\UpdateAction::class);
        $app->get('/config/check-files', Actions\Api\Config\CheckFilesAction::class);
        $app->get('/config/count-books-without-files', Actions\Api\Config\CountBooksWithoutFilesAction::class);
        $app->post('/config/clear-books-without-files', Actions\Api\Config\ClearBooksWithoutFilesAction::class);
        $app->get('/config/import-files', Actions\Api\Config\GetImportFilesAction::class);
        $app->post('/config/import-files', Actions\Api\Config\DoImportFilesAction::class);
        $app->get('/config/books-without-cover', Actions\Api\Config\GetBooksWithoutCoverAction::class);
        $app->post('/config/import-new-cover-from-pdf', Actions\Api\Config\DoImportNewCoverFromPdfAction::class);
    }
}
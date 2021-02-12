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
use Slim\Interfaces\RouteCollectorProxyInterface;

class Routes
{
    static public function register(App $app)
    {
        // pages
        $app->get('/', Actions\IndexPageAction::class);
        $app->get('/about', Actions\AboutPageAction::class);
        $app->get('/config', Actions\ConfigPageAction::class);
        // api
        $app->group('/api', function (RouteCollectorProxyInterface $group) {
            $group->get('/book/cover', Actions\Api\Cover\GetAction::class);
            $group->get('/book', Actions\Api\Book\ListAction::class);
            $group->post('/book/manage', Actions\Api\Book\ManageAction::class);
            $group->post('/book/cover-save', Actions\Api\Cover\UpdateAction::class);
            $group->get('/category', Actions\Api\Category\ListAction::class);
            $group->post('/category/manage', Actions\Api\Category\ManageAction::class);
            $group->post('/config', Actions\Api\Config\UpdateAction::class);
            $group->get('/config/check-files', Actions\Api\Config\CheckFilesAction::class);
            $group->get('/config/count-books-without-files', Actions\Api\Config\CountBooksWithoutFilesAction::class);
            $group->post('/config/clear-books-without-files', Actions\Api\Config\ClearBooksWithoutFilesAction::class);
            $group->get('/config/import-files', Actions\Api\Config\GetImportFilesAction::class);
            $group->post('/config/import-files', Actions\Api\Config\DoImportFilesAction::class);
            $group->get('/config/books-without-cover', Actions\Api\Config\GetBooksWithoutCoverAction::class);
            $group->post('/config/import-new-cover-from-pdf', Actions\Api\Config\DoImportNewCoverFromPdfAction::class);
        });
    }
}
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
        $app->get('/', Actions\IndexPageAction::class);
        $app->get('/about', Actions\AboutPageAction::class);
        $app->get('/config', Actions\ConfigPageAction::class);
        $app->group('/api', function (RouteCollectorProxyInterface $group) {
            $group->group('/book', function (RouteCollectorProxyInterface $group) {
                $group->get('', Actions\Api\Book\ListAction::class);
                $group->get('/cover', Actions\Api\Cover\GetAction::class);
                $group->post('/cover-save', Actions\Api\Cover\UpdateAction::class);
                $group->post('/manage', Actions\Api\Book\ManageAction::class);
            });
            $group->group('/category', function (RouteCollectorProxyInterface $group) {
                $group->get('', Actions\Api\Category\ListAction::class);
                $group->post('/manage', Actions\Api\Category\ManageAction::class);
            });
            $group->group('/config', function (RouteCollectorProxyInterface $group) {
                $group->post('', Actions\Api\Config\UpdateAction::class);
                $group->get('/check-files', Actions\Api\Config\CheckFilesAction::class);
                $group->get('/count-books-without-files', Actions\Api\Config\CountBooksWithoutFilesAction::class);
                $group->post('/clear-books-without-files', Actions\Api\Config\ClearBooksWithoutFilesAction::class);
                $group->get('/import-files', Actions\Api\Config\GetImportFilesAction::class);
                $group->post('/import-files', Actions\Api\Config\DoImportFilesAction::class);
                $group->get('/books-without-cover', Actions\Api\Config\GetBooksWithoutCoverAction::class);
                $group->post('/import-new-cover-from-pdf', Actions\Api\Config\DoImportNewCoverFromPdfAction::class);
            });
        });
    }
}
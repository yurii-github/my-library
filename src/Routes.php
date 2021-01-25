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
        $app->get('/api/book/cover', Actions\GetBookCoverAction::class);
        $app->get('/api/book', Actions\GetBookListAction::class);
        $app->post('/api/book/manage', Actions\ManageBookAction::class);
        $app->post('/api/book/cover-save', Actions\UpdateBookCoverAction::class);
        $app->get('/api/category', Actions\GetBookCategoryListAction::class);
        $app->post('/api/category/manage', Actions\ManageBookCategoryAction::class);
        $app->get('/about', Actions\Pages\AboutPageAction::class);
        $app->get('/config', Actions\Pages\ConfigPageAction::class);
        $app->post('/config/save', Actions\UpdateConfigAction::class);
        $app->post('/config/compact-database', Actions\ConfigCompactDatabaseAction::class);
        $app->get('/config/check-files', Actions\ConfigCheckFilesAction::class);
        $app->get('/config/count-books-without-files', Actions\ConfigCountBooksWithoutFilesAction::class);
        $app->post('/config/clear-books-without-files', Actions\ConfigClearBooksWithoutFilesAction::class);
        $app->get('/config/import-files', Actions\ConfigGetImportFilesAction::class);
        $app->post('/config/import-files', Actions\ConfigDoImportFilesAction::class);
        $app->get('/config/books-without-cover', Actions\ConfigGetBooksWithoutCoverAction::class);
        $app->post('/config/import-new-cover-from-pdf', Actions\ConfigDoImportNewCoverFromPdfAction::class);
    }
}
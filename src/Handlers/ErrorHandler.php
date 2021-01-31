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

namespace App\Handlers;

use App\Exception\UnsupportedOperationException;
use Illuminate\Validation\ValidationException;
use Psr\Http\Message\ServerRequestInterface;
use \Slim\Handlers\ErrorHandler as SlimErrorHandler;

class ErrorHandler extends SlimErrorHandler
{
    public function getDisplayErrorDetails(): bool
    {
        return $this->displayErrorDetails;
    }

    public function getRequest(): ServerRequestInterface
    {
        return $this->request;
    }

    protected function determineStatusCode(): int
    {
        if ($this->exception instanceof ValidationException) {
            return $this->exception->status;
        }
        
        if ($this->exception instanceof UnsupportedOperationException) {
            return 400;
        }

        return parent::determineStatusCode();
    }
}
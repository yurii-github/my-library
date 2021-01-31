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

namespace App\Renderers;

use Illuminate\Validation\ValidationException;
use Slim\Interfaces\ErrorRendererInterface;
use Throwable;

class JsonErrorRenderer implements ErrorRendererInterface
{
    public function __invoke(Throwable $exception, bool $displayErrorDetails): string
    {
        if ($exception instanceof ValidationException) {
            return json_encode($exception->errors(), JSON_UNESCAPED_UNICODE);
        }
        
        $error = [];
        if ($displayErrorDetails) {
            do {
                $error[] = $this->formatExceptionFragment($exception);
            } while ($exception = $exception->getPrevious());
        }

        return json_encode($error, JSON_UNESCAPED_UNICODE);
    }

    /**
     * @param Throwable $exception
     * @return array<string|int>
     */
    private function formatExceptionFragment(Throwable $exception): array
    {
        return [
            'type' => get_class($exception),
            'code' => $exception->getCode(),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        ];
    }
}
<?php declare(strict_types=1);
/*
 * My Book Library
 *
 * Copyright (C) 2014-2024 Yurii K.
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

namespace App\Actions\Api\Config;

use App\Actions\AbstractApiAction;
use App\Configuration\Configuration;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class UpdateAction extends AbstractApiAction
{
    protected Configuration $config;

    public function __construct(ContainerInterface $container)
    {
        $this->config = $container->get(Configuration::class);
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $post = $request->getParsedBody();

        // TODO: workaround, 1st step to better config update, do proper validation later
        $field = Arr::get($post, 'field');
        $value = Arr::get($post, 'value');
        $this->validate($field, $value);

        list($group, $attr) = explode('_', $field);
        $this->config->$group->$attr = $value;
        $this->config->save();

        return $this->asJSON();
    }

    protected function validate($field, $value)
    {
        if ($field === 'library_directory') {
            if (!Str::endsWith($value, ['/', '\\'])) {
                throw new \InvalidArgumentException("Library directory must end with a slash!");
            } elseif (!is_dir($value)) {
                throw new \InvalidArgumentException("Library directory must exist!");
            } elseif (!is_readable($value)) {
                throw new \InvalidArgumentException("Library directory must be readable!");
            }
        }
    }
}

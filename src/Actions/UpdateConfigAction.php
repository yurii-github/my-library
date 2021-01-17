<?php
/*
 * My Book Library
 *
 * Copyright (C) 2014-2020 Yurii K.
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
use Illuminate\Support\Arr;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class UpdateConfigAction
{
    /** @var Configuration */
    protected $config;


    public function __construct(ContainerInterface $container)
    {
        $this->config = $container->get(Configuration::class);
    }


    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $resp = new \stdClass();
        $resp->msg = '';
        $resp->result = false;
        $resp->title = '';

        $post = $request->getParsedBody();
        $field = Arr::get($post, 'field');
        $value = Arr::get($post, 'value');

        list($group, $attr) = explode('_', $field);

        
        try {
            if (!isset($this->config->$group->$attr)) {
                throw new \Exception("invalid property name '{$group}.{$attr}'");
            }
            $this->config->$group->$attr = $value;
            $this->config->save();
            $resp->msg = "<b>$attr</b> was successfully updated";
            $resp->result = true;
        } catch (\Exception $e) {
            $resp->msg = __FILE__ . ': ' . __LINE__ . ' ' . $e->getMessage();
            $resp->result = false;
        } finally {
            $resp->title = $group;
        }

        $response->getBody()->write(json_encode($resp));
        
        return $response->withHeader('Content-Type', 'application/json');
    }
}
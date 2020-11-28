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

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Psr\Http\Message\ServerRequestInterface;

class JGridRequestQuery
{
    protected $data;
    protected $query;


    public function __construct(Builder $query, ServerRequestInterface $request)
    {
        $params = $request->getQueryParams();
        $data = [
            'page' => $params['page'],
            'limit' => $params['rows'],
            'filters' => $params['filters'] ?? null,
            'sort_column' => $params['sidx'],
            'sort_order' => $params['sord'],
        ];
        $data['limit'] = empty($data['limit']) || $data['limit'] <= 0 || $data['limit'] > 30 ? 10 : $data['limit'];
        $data['page'] = empty($data['page']) || $data['page'] <= 0 ? 1 : $data['page'];

        $this->data = $data;
        $this->query = $query;
    }

    
    public function withFilters(): self
    {
        $filters = $this->data['filters'];
        $conditions = ['bw' => 'like', 'eq' => '='];

        if ($filters instanceof \stdClass && is_array($filters->rules)) {
            foreach ($filters->rules as $rule) {
                if (empty($conditions[$rule->op])) {
                    continue; // unknown condition, skip
                }
                if (!empty($filters->groupOp) && $filters->groupOp == 'AND') {
                    $this->query->where($rule->field, $conditions[$rule->op], (string)$rule->data); // TODO: better security
                } else {
                    $this->query->orWhere($rule->field, $conditions[$rule->op], (string)$rule->data); // TODO: better security
                }
            }
        }

        return $this;
    }


    public function withSorting($defaultColumn, $defaultOrder = 'asc'): self
    {
        $column = !empty($this->data['sort_column']) ? $this->data['sort_column'] : $defaultColumn;
        $order = !empty($this->data['sort_column']) && !empty($this->data['sort_order']) ? $this->data['sort_order'] : $defaultOrder;

        $this->query->orderBy($column, $order);

        return $this;
    }


    public function paginate(array $columns = ['*'])
    {
        $total = $this->query->count();
        $this->query
            ->offset(($this->data['page'] - 1) * $this->data['limit'])
            ->limit($this->data['limit']);
        $rows = $this->query->get()->transform(function (Model $item) use ($columns) {
            $pk = $item->getKeyName();
            return ['id' => $item->$pk, 'cell' => array_intersect_key($item->toArray(), array_flip($columns))];
        })->all();

        $response = new \stdClass();
        $response->page = $this->data['page'];
        $response->total = count($rows);
        $response->records = $total;
        $response->rows = $rows;

        return $response;
    }

}
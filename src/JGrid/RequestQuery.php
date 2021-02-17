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

namespace App\JGrid;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Psr\Http\Message\ServerRequestInterface;

class RequestQuery
{
    protected $data;
    protected $query;


    public function __construct(Builder $query, ServerRequestInterface $request)
    {
        $params = $request->getQueryParams();
        $data = [
            'page' => $params['page'] ?? null,
            'limit' => $params['rows'] ?? null,
            'filters' => $params['filters'] ?? null,
            'sort_column' => $params['sidx'] ?? null,
            'sort_order' => $params['sord'] ?? null,
        ];
        $data['limit'] = (int)((empty($data['limit']) || $data['limit'] <= 0 || $data['limit'] > 50) ? 50 : $data['limit']);
        $data['page'] = (int)((empty($data['page']) || $data['page'] <= 0) ? 1 : $data['page']);

        $this->data = $data;
        $this->query = $query;
    }

    public function withFilters(): self
    {
        $filters = json_decode($this->data['filters']);
        if (!$this->validateFilters($filters)) {
            return $this;
        }

        $conditions = ['bw' => 'like', 'eq' => '='];
        foreach ($filters->rules as $rule) {
            if (empty($conditions[$rule->op])) {
                continue; // unknown condition, skip
            }
            $operator = $conditions[$rule->op];
            $groupOperator = $filters->groupOp ?? 'OR';
            $this->applyRuleClause($this->query, $groupOperator, $rule->field, $operator, $rule->data);
        }

        return $this;
    }

    protected function applyRuleClause(Builder $query, string $groupOperator, string $field, string $operator, $value)
    {
        $likeModifier = $this->likeModifier($operator);
        $value = $likeModifier . $value . $likeModifier;

        if ($groupOperator === 'AND') {
            $query->where($field, $operator, $value);
        } else {
            $query->orWhere($field, $operator, $value);
        }
    }

    protected function likeModifier(?string $operator): string
    {
        return $operator === 'like' ? '%' : '';
    }

    /**
     * @param mixed $filters
     * @return bool
     */
    protected function validateFilters($filters): bool
    {
        if (empty($filters)) {
            return false;
        }

        return $filters instanceof \stdClass && is_array($filters->rules);
    }


    public function withSorting($defaultColumn, $defaultOrder = 'asc'): self
    {
        $column = !empty($this->data['sort_column']) ? $this->data['sort_column'] : $defaultColumn;
        $order = !empty($this->data['sort_column']) && !empty($this->data['sort_order']) ? $this->data['sort_order'] : $defaultOrder;

        $this->query->orderBy($column, $order);

        return $this;
    }


    public function paginate(array $columns = ['*']): array
    {
        $total = $this->query->count();
        $this->query
            ->offset(($this->data['page'] - 1) * $this->data['limit'])
            ->limit($this->data['limit']);
        $rows = $this->query->get()->transform(function (Model $item) use ($columns) {
            $pk = $item->getKeyName();
            return ['id' => $item->$pk, 'cell' => array_intersect_key($item->toArray(), array_flip($columns))];
        })->all();

        return [
            'page' => $this->data['page'],
            'rows' => $rows,
            'total' => ceil($total / $this->data['limit']),
            'records' => $total
        ];
    }

}
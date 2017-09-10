<?php
/*
 * My Book Library
 *
 * Copyright (C) 2014-2017 Yurii K.
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

namespace app\components;

use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;

class ActiveRecord extends \yii\db\ActiveRecord
{
    /**
     * @param array $data
     * @param array $nameColumns columns to select
     * @param array $sortColumns columns we allow to sort
     * @param ActiveQuery $query
     * @return \stdClass
     */
    protected static function jgridRecords(array $data, $nameColumns, $sortColumns, ActiveQuery $query)
    {
        $query = self::jqgridPepareQuery($query, $data, $sortColumns);

        $data['limit'] = empty($data['limit']) || $data['limit'] <= 0 || $data['limit'] > 30 ? 10 : $data['limit'];
        $data['page'] = empty($data['page']) || $data['page'] <= 0 ? 1 : $data['page'];

        $provider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $data['limit'],
                'page' => $data['page'] - 1 //jgrid fix
            ],
        ]);

        $cells = [];
        /** @var ActiveRecord $item */
        foreach ($provider->getModels() as $item) {
            $pk = $item->primaryKey()[0];
            $cells[] = ['id' => $item->$pk, 'cell' => array_values($item->getAttributes($nameColumns))];
        }

        return self::jqgridPrepareResponse($provider->getPagination()->getPage(), $provider->getPagination()->getPageCount(), $provider->getTotalCount(), $cells);
    }

    protected static function jqgridPrepareResponse($page, $total, $records, $rows)
    {
        $response = new \stdClass();
        $response->page = $page + 1; // NOTE: jqGrid fix
        $response->total = $total;
        $response->records = $records;
        $response->rows = $rows;
        return $response;
    }

    /**
     * @param ActiveQuery $query
     * @param array $data
     * @param array $sortColumns columns we allow sorting
     * @return ActiveQuery
     */
    protected static function jqgridPepareQuery(ActiveQuery $query, array $data, $sortColumns)
    {
        //defaults
        $data['sort_column'] = empty($data['sort_column']) ? $sortColumns[0] : $data['sort_column'];
        $data['sort_order'] = !empty($data['sort_order']) && $data['sort_order'] == 'desc' ? SORT_DESC : SORT_ASC; //+secure
        $filters = empty($data['filters']) ? null : json_decode($data['filters']);
        $conditions = ['bw' => 'like', 'eq' => '='];

        if ($filters instanceof \stdClass && is_array($filters->rules)) {
            foreach ($filters->rules as $rule) {
                if (empty($conditions[$rule->op])) {
                    continue; // unknown condition, skip
                }
                if (!empty($filters->groupOp) && $filters->groupOp == 'AND') {
                    $query->andFilterWhere([$conditions[$rule->op], $rule->field, $rule->data]);
                } else {
                    $query->orFilterWhere([$conditions[$rule->op], $rule->field, $rule->data]);
                }
            }
        }
        if (in_array($data['sort_column'], $sortColumns)) {
            $query->orderBy([$data['sort_column'] => $data['sort_order']]);
        }

        return $query;
    }
}
<?php
/*
 * My Book Library
 *
 * Copyright (C) 2014-2019 Yurii K.
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

namespace app\models;

use app\components\ActiveRecord;
use Yii;
use app\Helpers\Tools;

/**
 * Category entity AR
 *
 * @property string $guid primary key
 * @property string $title
 */
class Categories extends ActiveRecord
{
    /**
     * @var int virtual field, used for marking books that use this category
     */
    public $marker;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['guid'], 'string', 'max' => 36],
            [['title'], 'string', 'max' => 255],
            [['guid'], 'unique'],
        ];
    }

    public function scenarios()
    {
        return [
            self::SCENARIO_DEFAULT => ['title']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'guid' => Yii::t('app', 'Category Guid'),
            'title' => Yii::t('app', 'Category Title'),
            'marker' => 'marker'
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        // yii2 event handling logic. do not remove!
        //if (!parent::beforeSave($insert)) { return false; }

        if ($insert) {
            $this->guid = Tools::com_create_guid();
        }

        return true;
    }

    /**
     * gets records in jqGrid format
     *
     * @param array $data
     * @return \stdClass
     */
    public static function jgridCategories(array $data)
    {
        $sortColumns = $nameColumns = ['title', 'marker'];

        $query = self::find()
            ->leftJoin('books_categories bc', 'bc.category_guid = categories.guid AND bc.book_guid = :guid', [':guid' => $data['nodeid']])
            ->select(['guid', 'title', 'CASE WHEN bc.category_guid IS NOT NULL THEN 1 ELSE 0 END AS [[marker]]']);

        return self::jgridRecords($data, $nameColumns, $sortColumns, $query);
    }

}

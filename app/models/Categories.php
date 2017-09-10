<?php

namespace app\models;

use app\components\ActiveRecord;
use Yii;
use app\helpers\Tools;

/**
 * This is the model class for table "categories".
 *
 * @property string $guid
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
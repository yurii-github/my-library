<?php

namespace app\models;

use Yii;
use app\helpers\Tools;

/**
 * This is the model class for table "categories".
 *
 * @property string $category_guid
 * @property string $category_title
 */
class Categories extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'categories';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['category_guid'], 'string', 'max' => 36],
            [['category_title'], 'string', 'max' => 255],
            [['category_guid'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'category_guid' => Yii::t('app', 'Category Guid'),
            'category_title' => Yii::t('app', 'Category Title'),
        ];
    }
    
    
    public function beforeSave($insert) {
      // yii2 event handling logic. do not remove!
      //if (!parent::beforeSave($insert)) { return false; }
      
      if ($insert) {
        $this->category_guid = Tools::com_create_guid();
      }
      
      return true;
    }
}

<?php
namespace app\models;

use Yii;
use yii\base\Model;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;
use yii\web\Response;
use yii\web\UploadedFile;


/**
 * @property string $name
 * @property int $id
*/
class Publishers extends ActiveRecord
{
	public function getBooks()
	{
		$q = $this->hasMany(Books::className(), ['publisher_id' => 'publisher_id']);
		return $q;
	}
}
	
	
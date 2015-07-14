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
 * @property string $created_date
 * @property string $updated_date
 * @property string $book_guid
 * @property string $filename
 * @property string $book_cover binary cover or yii\web\UploadedFile before save!
 */
class Books extends ActiveRecord
{	
	
	/**
	 * (non-PHPdoc)
	 * @see \yii\base\Model::rules()
	 */
	public function rules()
	{
		return [
			// - cover (get)
			[['book_cover'], 'string', 'on' => ['cover']],
			// - edit (update)
			[['year'], 'integer', 'on' => ['edit']],
			[['favorite'], 'number', 'on' => ['edit']],
			[['favorite', 'read', 'year', 'title', 'isbn13', 'author', 'publisher', 'ext'], 'safe', 'on' => 'edit'],
			['book_cover', 'image', 'skipOnEmpty' => true, 'extensions' => 'gif,jpg,png', 'on' => ['edit'] ],
			// - filter (get)
			[['title', 'publishers.name'], 'string', 'on' => ['filter'] /*  'message' => 'must be integer!'*/],
			// - import (from fs, get)
			[['title', 'filename'], 'safe', 'on' => 'import'],
			// add (insert)
			[['created_date', 'updated_date', 'book_guid', 'favorite', 'read', 'year', 'title', 'isbn13', 'author', 'publisher', 'ext', 'filename'], 'safe', 'on' => 'add']
		];
	}
	
	
	/**
	 * resamples image to match boundary limits by width. Height is not checked and will resampled according to width's change percentage
	 *
	 * @param string $img_blob image source as blob string
	 * @param int $max_width max allowed width for picture in pixels
	 * 
	 * @return string image as string BLOB
	 */
	static public function getResampledImageByWidthAsBlob($img_blob, $max_width = 800)
	{
		list($src_w, $src_h) = getimagesizefromstring($img_blob);
	
		$src_image = imagecreatefromstring($img_blob);
		$dst_w = $src_w > $max_width ? $max_width : $src_w;
		$dst_h = $src_w > $max_width ? ($max_width/$src_w*$src_h) : $src_h; //minimize height in percent to width
		$dst_image = imagecreatetruecolor($dst_w, $dst_h);
		imagecopyresized($dst_image, $src_image, 0, 0, 0, 0, $dst_w, $dst_h, $src_w, $src_h);
		ob_start();
		imagejpeg($dst_image);
		return ob_get_clean();
	}
	
	
	/**
	 * generates global unique id
	 *
	 * format: hhhhhhhh-hhhh-hhhh-hhhh-hhhhhhhhhhhh
	 *
	 * @return string GUID
	 */
	static public function com_create_guid()
	{
		mt_srand((double)microtime()*10000);
		$charid = strtoupper(md5(uniqid(rand(), true)));
		return substr($charid, 0, 8).'-'.substr($charid, 8, 4).'-'.substr($charid,12, 4).'-'.substr($charid,16, 4).'-'.substr($charid,20,12);
	}
	
	
	public function buildFilename()
	{
		// TODO: filesystem security
		return str_replace(array(
			'{year}',
			'{title}',
			'{publisher}',
			'{author}',
			'{isbn13}',
			'{ext}'
		), array(
			$this->year,
			$this->title,
			$this->publisher,
			$this->author,
			$this->isbn13,
			$this->ext
		), \Yii::$app->mycfg->book->nameformat);
	}
	
	
	public function behaviors()
	{
		return [
			'autotime'=> [
				'class' => TimestampBehavior::className(),
				'createdAtAttribute' => 'created_date',
				'updatedAtAttribute' => 'updated_date',
				'value' => function() {
					$r = \Yii::$app->formatter->asDatetime('now','php:Y-m-d H:i:s');
					return $r;
					//var_dump($r);die;
				}
			]
		];
	}
	


	
	public function beforeDelete()
	{
		if (parent::beforeDelete()) {// ...custom code here...
			if (\Yii::$app->mycfg->library->sync && !file_exists(\Yii::$app->mycfg->Encode(\Yii::$app->mycfg->library->directory.'/'.$this->filename))) {
				\Yii::warning('file "'. $this->filename .'" was removed before record deletion with sync enabled');
			}
			return true;
		} else {
			return false;
		}
	}
	
	
	public function afterDelete()
	{
		if (\Yii::$app->mycfg->library->sync) {
			unlink(\Yii::$app->mycfg->Encode(\Yii::$app->mycfg->library->directory.'/'.$this->filename));
		}
		parent::afterDelete();
	}
	
				
				
	public function beforeSave($insert)
	{
		if (parent::beforeSave($insert)) { // ...custom code here...
			$new_filename = $this->buildFilename();
			if ($this->book_cover) {//resize
				$this->book_cover = self::getResampledImageByWidthAsBlob($this->book_cover, \Yii::$app->mycfg->book->covermaxwidth);
			}
			
			if($insert) {//inserting, make guid
				$this->book_guid = self::com_create_guid();
				if ($this->getScenario() != 'import') {
					$this->filename = $new_filename;
				}
			} else { //updating
				//syncing
				if (!empty($this->filename) && \Yii::$app->mycfg->library->sync) {
					//TODO: better combine
					if ($this->filename != $new_filename) { // update file in filesystem
						//check file exists
						if (!file_exists(\Yii::$app->mycfg->library->directory . $this->filename)) {
							throw new \Exception("Sync for file failed. Source file '$this->filename' does not exist");
						}
						if (!rename(
							\Yii::$app->mycfg->Encode(\Yii::$app->mycfg->library->directory . $this->filename),
							\Yii::$app->mycfg->Encode(\Yii::$app->mycfg->library->directory . $new_filename))) {
								throw new \Exception('Sync for file failed.<br /><br />' . \error_get_last()['message']);
						}
					}
				}
				$this->filename = $new_filename;
			}
			return true;
		} else {
			return false;
		}
	}
	
	
	protected static function jqgridPepareQuery(array $data)
	{
		//defaults
		$data['sort_column'] = empty($data['sort_column']) ? 'created_date' : $data['sort_column'];
		$data['sort_order'] = !empty($data['sort_order']) &&  $data['sort_order']  == 'desc' ? SORT_DESC : SORT_ASC; //+secure
		$filters = empty($data['filters']) ? null : json_decode($data['filters']);
		$query = Books::find();
		
		if ($filters instanceof \stdClass && ! empty($filters->rules)) {
			$conditions = ['bw'=>'like','eq'=>'='];
			foreach ($filters->rules as $rule) {
				if ($filters->groupOp == 'AND') {
					$query->andFilterWhere([$conditions[$rule->op], $rule->field, $rule->data]);
				} else {
					$query->orFilterWhere([$conditions[$rule->op], $rule->field, $rule->data]);
				}
			}
		}
		if (in_array($data['sort_column'], ['favorite', 'read', 'year', 'title', 'created_date', 'isbn13', 'author', 'publisher'])) {
			$query->orderBy([$data['sort_column'] => $data['sort_order'] ]);
		}
		$query->select(['created_date', 'book_guid', 'favorite', 'read', 'year', 'title', 'isbn13', 'author', 'publisher', 'ext', 'filename']);
		
		return $query;
	}
	
	protected static function jqgridPrepareResponse($page, $total, $records, $rows)
	{
		$response = new \stdClass();
		$response->page = $page + 1; //jgrid fix
		$response->total = $total;
		$response->records = $records;
		$response->rows = $rows;
		return $response;
	}
	
	
	/**
	 * 
	 * @param array $data [page, limit, sort_column, sort_order, filters=json] 
	 * @return multitype:multitype:Ambigous <NULL> multitype:unknown string   |\stdClass
	 */
	public static function jgridBooks(array $data)
	{
		$query = self::jqgridPepareQuery($data);
		$data['limit'] = empty($data['limit']) || $data['limit'] <= 0 || $data['limit'] > 30 ? 10 : $data['limit'];
		$data['page'] = empty($data['page']) || $data['page'] <= 0 ? 1 : $data['page'];
		
		$provider = new ActiveDataProvider([
			'query' => $query,
			'pagination' => [
				'pageSize' => $data['limit'],
				'page' => --$data['page'] //jgrid fix
			],
		]);
		
		$books = $provider->getModels();

		$to_array = function ($obj_arr, $query) {
			$ar = [];
			foreach ($obj_arr as $o) {
				/* @var $o Books */
				$book = [];
				$attr = $o->getAttributes($o->fields());
				//jgrid required no assoc array same order
				foreach ($query->select as $col) {
					if ($col == 'created_date') {
						$book[] = \Yii::$app->formatter->asDate($attr[$col], 'php:d-m-Y');
					} else {
						$book[] = $attr[$col];
					}
				}
				$ar[] = ['id' => $attr['book_guid'], 'cell' => $book];
			}
			return $ar;
		};
		
		
		return self::jqgridPrepareResponse($provider->getPagination()->getPage(), $provider->getPagination()->getPageCount(), $provider->getTotalCount(), $to_array($books, $query));
	}
	
	public function attributeLabels()
	{
		return [
			'title' => 'title',
			'created_date' => 'created',
			'updated_date' => 'updated',
			'publishers.name' => 'publisher'
			
		];
	}
		
	
	public static function getCover($id)
	{
		header("Cache-Control: no-cache");
		header("Pragma: no-cache");
		header('Content-Type: image/jpeg');
		\Yii::$app->response->format = Response::FORMAT_RAW;
		$cache_name = 'book-cover-' . (empty($id) ? 'empty' : $id);
		
		if (\Yii::$app->cache->exists($cache_name)) {
			return \Yii::$app->cache->get('book-cover-'.$book_guid); //NOTE: DISABLE WHILE TESTING
		}
		 
		$book = self::find()->select(['book_cover'])->where('book_guid = :book_guid', ['book_guid' => $id])->asArray()->one();
		
		if (empty($book['book_cover'])) {
			$book['book_cover'] = file_get_contents(\Yii::getAlias('@webroot').'/assets/app/book-cover-empty.jpg');
			\Yii::$app->cache->set('book-cover-empty', $book['book_cover']); //don't cache empty
		} else {
			\Yii::$app->cache->set($cache_name, $book['book_cover'], 3600);
		}
		
		return $book['book_cover'];
	}
	
	
	public function attributes()
	{
		return parent::attributes();
		//eturn array_merge(parent::attributes(), ['publishers.name']);
	}


}


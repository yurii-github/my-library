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
	// public function tableName() {}
	
	
	/**
	 * resamples image to match boundary limits by width. Height is not checked and will resampled according to width's change percentage
	 *
	 * @param string $img_blob image source as blob string
	 * @param int $max_width max allowed width for picture in pixels
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
		$hyphen = chr(45);// "-"
		$uuid = substr($charid, 0, 8).$hyphen
		.substr($charid, 8, 4).$hyphen
		.substr($charid,12, 4).$hyphen
		.substr($charid,16, 4).$hyphen
		.substr($charid,20,12);
			
		return $uuid;
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
				'value' => function() { return \Yii::$app->formatter->asDatetime('now','php:Y-m-d H:i:s'); }
			]
		];
	}
	
	public function rules()
	{
		return [
			// edit
			[['year', 'favorite'], 'integer', 'on' => ['edit']],
			
			[['title', 'publishers.name'], 'string', 'on' => ['filter'] /*  'message' => 'must be integer!'*/],
			
			['book_cover', 'image', 'skipOnEmpty' => true, 'extensions' => 'gif,jpg,png', 'on' => ['edit'] ],
			
			//import from fs
			[['title', 'filename'], 'safe', 'on' => 'import'],
			
			// add
			[['created_date', 'updated_date', 'book_guid', 'favorite', 'read', 'year', 'title', 'isbn13', 'author', 'publisher',
			 'ext', 'filename'], 'safe', 'on' => 'add'],
			
			[['updated_date', 'favorite', 'read', 'year', 'title', 'isbn13', 'author',
			 'publisher', 'ext', 'filename'], 'safe', 'on' => 'edit']
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
			if ($this->book_cover instanceof UploadedFile && !empty($this->book_cover->tempName)) {//resize
				$this->book_cover = self::getResampledImageByWidthAsBlob(file_get_contents($this->book_cover->tempName), \Yii::$app->mycfg->book->covermaxwidth);
			} else {
				unset($this->book_cover); //dont remove if not set
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
						if (!file_exists($this->filename)) {
							throw new \Exception('Sync for file failed. Source file does not exist');
						}
						if (!rename(
							\Yii::$app->mycfg->Encode(\Yii::$app->mycfg->library->directory.'/'.$this->filename),
							\Yii::$app->mycfg->Encode(\Yii::$app->mycfg->library->directory.'/'.$new_filename))) {
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
	
	
	/**
	 * 
	 * @param array $data [page, limit, sort_column, sort_order, filters=json] 
	 * @return multitype:multitype:Ambigous <NULL> multitype:unknown string   |\stdClass
	 */
	public static function jgridBooks(array $data)
	{
		//defaults
		$data['sort_column'] = empty($data['sort_column']) ? 'created_date' : $data['sort_column'];
		$data['sort_order'] = !empty($data['sort_order']) &&  $data['sort_order']  == 'desc' ? SORT_DESC : SORT_ASC; //+secure
		$data['limit'] = empty($data['limit']) || $data['limit'] <= 0 || $data['limit'] > 30 ? 10 : $data['limit'];
		$data['page'] = empty($data['page']) || $data['page'] <= 0 ? 1 : $data['page'];
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
		
		$response = new \stdClass();
		$response->page = $provider->getPagination()->getPage()+1;//jgrid fix
		$response->total = $provider->getPagination()->getPageCount();
		$response->records = $provider->getTotalCount();
		$response->rows = $to_array($books, $query);
		
		return $response;
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
	
	
	public function beforeValidate()
	{
		$this->book_cover = UploadedFile::getInstance($this, 'book_cover'); // Yii doesn't do it for us
		return parent::beforeValidate();
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
	
	
	
	public function getPublishers()
	{
		/* @var $q \yii\db\ActiveQuery */
		$q = $this->hasOne(Publishers::className(), ['publisher_id' => 'publisher_id']);
		
		//$q->eagerLoading = true;
		return $q;
	}
	
	public function attributes()
	{
	
		return array_merge(parent::attributes(), ['publishers.name']);
	}
	
	
	

}


<?php
namespace app\components;

use yii\caching\Cache;
class UrlManager extends \yii\web\UrlManager
{

	public $use_cache = false;

	/**
	 * Initializes UrlManager.
	 *
	 *
	 * ADDED: makes cache use optional
	 */
	public function init()
	{
		if (!$this->enablePrettyUrl || empty($this->rules)) {
			return;
		}
		
		if (!$this->use_cache) { // A don't use cache
			$this->rules = $this->buildRules($this->rules);
			return;
		}
		
		// B use cache
		$cacheKey = __CLASS__;
		if (is_string($this->cache)) {
			$this->cache = \Yii::$app->get($this->cache, false);
		}
		if ($this->cache instanceof Cache) {
			$hash = md5(json_encode($this->rules));
			if (($data = $this->cache->get($cacheKey)) !== false && isset($data[1]) && $data[1] === $hash) {
				$this->rules = $data[0];
			} else {
				$this->rules = $this->buildRules($this->rules);
				$this->cache->set($cacheKey, [$this->rules,	$hash]);
			}
		}
	}
	
}


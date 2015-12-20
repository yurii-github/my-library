<?php
namespace app\components;

class ApcCache extends \yii\caching\ApcCache
{
	
	public function init()
	{
		$this->useApcu = true;
		parent::init();
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \yii\caching\Cache::buildKey()
	 * @param $key array|string if string use as is, if array, implode with md5 hashing
	 */
    public function buildKey($key)
    {
    	if (is_array($key)) {
    		$key = $key[0].':'.md5(implode('', $key));
    	}
        return $this->keyPrefix . $key;
    }
    
}
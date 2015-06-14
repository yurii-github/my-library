<?php

use app\components\AssetManager;


class mockAssetManager extends AssetManager
{
	public function publishFile($src)
	{
		return parent::publishFile($src);
	}
}

class AssetManagerTest extends \tests\AppTestCase
{
	/**
	 * @expectedException \yii\base\Exception
	 */
	public function test_publishFile()
	{
		$manager = new mockAssetManager(['basePath' => \Yii::getAlias('@app/public/assets')]);
		$manager->publishFile('src');
	}
	
	
	/**
	 * @expectedException \yii\base\Exception
	 */
	public function test_publishDirectory()
    {
    	$this->initAppFileSystem();
    	
    	$manager = new mockAssetManager(['basePath' => \Yii::getAlias('@app/public/assets')]);
    	$manager->publishDirectory('src', []);
    }

}
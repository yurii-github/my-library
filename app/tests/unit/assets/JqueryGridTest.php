<?php
namespace tests\assets;

use yii\web\View;
use app\assets\JqueryGrid;

class JqueryGridTest extends \tests\AppTestCase
{
	protected function setUp(): void
	{
		$this->mockYiiApplication([
			'components' => [
				'assetManager' => [
					'class' => \yii\web\AssetManager::class,
					'basePath' => '@app/public/assets',
					'baseUrl' => '/assets'
				]
			]
		]);
	}

	public function pSupportedLangs()
	{
		return [
			['unknown', '/grid.locale-en.js$/'], //default
			['en-US', '/grid.locale-en.js$/'],
			['uk-UA', '/grid.locale-ua.js$/'],
		];
	}


	public function testInit()
	{
		$view = new View();
		JqueryGrid::register($view);

		$this->assertEquals(3, count($view->assetBundles));
		//$this->assertArrayHasKey(\app\assets\Jquery::class, $view->assetBundles);
		$this->assertArrayHasKey(\app\assets\JqueryUI::class, $view->assetBundles);
		$this->assertArrayHasKey(\app\assets\JqueryGrid::class, $view->assetBundles);

		$asset = $view->assetBundles[\app\assets\JqueryGrid::class];
		$this->assertEquals(1, count($asset->css));
		$this->assertEquals(2, count($asset->js));
	}


	/**
	 * @dataProvider pSupportedLangs
	 */
	public function testInit_language($lang, $regexp)
	{
		\Yii::$app->language = $lang;
		$view = new View();
		JqueryGrid::register($view);
		$asset = $view->assetBundles[\app\assets\JqueryGrid::class];

		$this->assertRegExp($regexp, $asset->js[1]);
	}


}

<?php
namespace app\components;

abstract class Controller extends \yii\web\Controller
{
	public function __construct($id, $module, array $config = [])
	{
		parent::__construct($id, $module, $config);
	}
}
<?php
namespace app\components;

class Security extends \yii\base\Security
{
	public $cost = 4; // 4-31
	public $hashAlgorithm = CRYPT_SHA512;
	
	/*
	 * @deprecated UNUSED
	 */
	public $passwordHashStrategy;
	
	/**
	 * (non-PHPdoc)
	 * @see \yii\base\Security::generatePasswordHash()
	 * @param $cost is UNUSED. use Security::$cost instead
	 */
	public function generatePasswordHash($password, $cost = null)
	{
		$hash = password_hash($password, $this->hashAlgorithm, ['cost' => $this->cost]);
		
		if ($hash === false) {
			\Yii::error('failed generatePasswordHash. TODO:  error_get_last()');
			throw new Exception('Unknown error occurred while generating hash.');
		}
		
		return $hash;
	}
	
	public function validatePassword($password, $hash)
	{
		return password_verify($password, $hash);
	}
	
}
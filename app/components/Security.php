<?php
namespace app\components;


use yii\base\Exception;

/**
 * 
 * @property $passwordHashStrategy is DEPRECTED and UNUSED
 *
 */
class Security extends \yii\base\Security
{
	public $cost = 4; // 4-31
	public $hashAlgorithm = CRYPT_SHA512;

	/**
	 * (non-PHPdoc)
	 * @see \yii\base\Security::generatePasswordHash()
	 * 
	 * @param $cost is UNUSED. use Security::$cost instead
	 * @throws Exception on bad password format or bad algorythm
	 */
	public function generatePasswordHash($password, $cost = null)
	{
		return password_hash($password, $this->hashAlgorithm, ['cost' => $this->cost]);
	}
	
	public function validatePassword($password, $hash)
	{
		return password_verify($password, $hash);
	}
	
}
<?php
namespace app\models;

use yii\db\ActiveRecord;
use yii\web\IdentityInterface;


/**
 * 
 * @property string  $username
 * @property string $password
 * @property string $auth_key
 * @property string $access_token
 * 
 */
class Users extends ActiveRecord implements IdentityInterface
{
	// - - - - IdentityInterface - - - - -
	/**
	 * finds an identity by the given ID
	 * @param string $id username
	 * @return Users|NULL
	 */
	public static function findIdentity($id)
	{
		return self::findOne(['username' => $id]);
	}
	
	public static function findIdentityByAccessToken($token, $type = null) 
	{
		return self::findOne(['access_token' => $token]);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \yii\web\IdentityInterface::getId()
	 * @return string
	 */
	public function getId()
	{
		return $this->username;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \yii\web\IdentityInterface::getAuthKey()
	 */
	public function getAuthKey()
	{
		return $this->auth_key;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \yii\web\IdentityInterface::validateAuthKey()
	 */
	public function validateAuthKey($authKey)
	{
		return ($this->getAuthKey() === $authKey);
	}
	
	// ---------------------------
	
	/**
	 * 
	 * @param string $username
	 * @return \common\models\Users|null
	 */
	public static function getUserByUsername($username)
	{
		return self::findIdentity($username);
	}
	
	/**
	 * 
	 * @param unknown $password
	 * @throws \Exception
	 * @return boolean
	 */
	public function validatePassword($password)
	{
		return \Yii::$app->getSecurity()->validatePassword($password, $this->password);
	}
	
	
	public function beforeSave($insert)
	{
		if (parent::beforeSave($insert)) {
			if ($this->isNewRecord) {
				$this->auth_key = \Yii::$app->getSecurity()->generateRandomString();
				$this->password = \Yii::$app->getSecurity()->generatePasswordHash($this->password, 4); //TODO: set to 10 or make as parameter
			}
			return true;
		}
	
		return false;
	}
	
	
}
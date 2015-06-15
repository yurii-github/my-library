<?php
namespace app\components\behaviors;

use yii\base\Behavior;
use yii\base\Event;
use yii\validators\Validator;


class EmailSupport extends Behavior
{
	
	/**
	 * 
	 * @param array $opt [data, subject, type]
	 * type = 'notification' etc
	 */
	public function sendEmail(array $opt)
	{
		if (empty($opt['data']) || empty($opt['subject']) || empty($opt['type'])) {
			throw new \yii\base\InvalidParamException('$opt must be an array and contain [data, subject, type]');
		}
		
		if (empty($this->owner)) {
			throw new \yii\base\InvalidValueException('owner was not set. this behavior cannot be used directly.');
		}

		$message = \Yii::$app->mailer->compose(
			['html' => $opt['type'].'/html','text' => $opt['type'].'/text'],
			['title' => $opt['subject'], 'notification' => $opt['data']]);
		$message->setFrom(\Yii::$app->mycfg->system->emailto)->setTo(\Yii::$app->mycfg->system->emailto)->setSubject($opt['subject']);
		

		if(!\Yii::$app->mycfg->system->email) {
			\Yii::trace('email support is disabled in config');
			return false;
		}
		
		return $message->send();
	}
	
}
<?php
namespace app\components\behaviors;

use yii\base\Behavior;
use yii\base\Event;
use yii\validators\Validator;

class EmailEvent extends Event
{
	/**
	 * @var \yii\swiftmailer\Message  email message
	 */
	public $message;
	
	/**
	 * 
	 * @var bool checks in before send event if send it or not
	 */
	public $allow_send = true;
	public $is_sent = false;
}

class EmailSupport extends Behavior
{	
	const EVENT_EMAIL_BEFORE_SEND = 'event_email_before_send';
	const EVENT_EMAIL_AFTER_SEND = 'event_email_after_send';
	
	public function events()
	{
		return [
			self::EVENT_EMAIL_BEFORE_SEND => 'event_email_beforeSend',
			self::EVENT_EMAIL_AFTER_SEND => 'event_email_afterSend'
		];
	}
	
	/**
	 * @param EmailEvent $e
	 */
	public function event_email_beforeSend($e)
	{
		\Yii::trace('event_email_beforeSend', 'events');
		if(!\Yii::$app->mycfg->system->email) {
			$e->allow_send = false;
			\Yii::trace('event_email_beforeSend: disabled in config', 'events');
			return;
		}
		
		foreach ($e->message->getTo() as $email => $name) {
			if (!preg_match('/^(.*<?)(.*)@(.*?)(>?)$/', $email, $matches)) {
				$e->allow_send = false;
				\Yii::trace('event_email_beforeSend: recipient email is in bad format', 'events');
				break;
			}
		}		
	}
	
	
	/**
	 * @param EmailEvent $e
	 */
	public function event_email_afterSend($e)
	{
		\Yii::trace('event_email_afterSend', 'events');
		echo $e->is_sent;
	}

	
	/**
	 * 
	 * @param array $opt [data, subject]
	 */
	public function sendEmail($opt = [])
	{
		$opt['type'] = 'notification'; //TODO: more email types

		$e = new EmailEvent();
		$e->message = \Yii::$app->mailer->compose(
			['html' => $opt['type'].'/html','text' => $opt['type'].'/text'],
			['title' => $opt['subject'], 'notification' => $opt['data']]);
		$e->message->setFrom(\Yii::$app->mycfg->system->emailto)->setTo(\Yii::$app->mycfg->system->emailto)->setSubject($opt['subject']);
		//$msg->getSwiftMessage()->getHeaders()->addTextHeader('n1', 'v1');
		$this->owner->trigger(self::EVENT_EMAIL_BEFORE_SEND, $e);
		
		if($e->allow_send) {
			$e->is_sent = $e->message->send();			
			$this->owner->trigger(self::EVENT_EMAIL_AFTER_SEND, $e);
		}
		
	}
}
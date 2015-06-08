<?php
namespace app\components;

class TranslationEventHandler
{

	public static function handleMissingTranslation(\yii\i18n\MissingTranslationEvent $event)
	{
		//$event->translatedMessage = "@MISSING: {$event->category}.{$event->message} FOR LANGUAGE {$event->language} @";
	}
}
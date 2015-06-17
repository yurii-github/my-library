<?php
namespace tests\components\behaviors;

use app\components\behaviors\EmailSupport;
use Composer\Autoload\ClassLoader;


class EmailSupportTest extends \tests\AppTestCase
{
	protected function setUp()
	{
		parent::setUp();
		$this->mockYiiApplication(['components' => [
			'mailer' => [
				'class' => \yii\swiftmailer\Mailer::class,
				'useFileTransport' => true,
				'viewPath' => '@app/emails',
				'fileTransportPath' => '@runtime/mail',
				'htmlLayout' => false,
				'textLayout' => false,
				'transport' => [
					'class' => \Swift_SmtpTransport::class,
					'host' => 'smtp.sample.com',
					'username' => 'test',
					'password' => 'test',
					'port' => '465',
					'encryption' => 'ssl' // ssl | tls
				]
			]
		]]);
	}
	
	/**
	 * @expectedException yii\base\InvalidParamException
	 */
	public function test_SendMail_badParams()
	{
		$es = new EmailSupport();
		$es->sendEmail(['subject' => 'asd']);
	}
	
	/**
	 * @expectedException yii\base\InvalidValueException
	 */
	public function test_SendMail_noBehaviorOwner()
	{
		$es = new EmailSupport();
		$es->sendEmail(['data' => 'msg content', 'subject' => 'msg subject', 'type' => 'notification']);
	}
	
	/**
	 * @expectedException Swift_RfcComplianceException
	 */
	public function test_SendMail_BadMailFormat()
	{
		$es = new EmailSupport();
		$owner = new \yii\base\Component();
		$owner->attachBehaviors(['email' => $es]);
		
		file_put_contents($this->getBaseFileSystem().'/emails/notification/html.php','html test');
		file_put_contents($this->getBaseFileSystem().'/emails/notification/text.php','text test');
		
		\Yii::$app->mycfg->system->email = true;
		\Yii::$app->mycfg->system->emailto = 'bad-email-format';
		$this->assertFalse($owner->sendEmail(['data' => 'msg content', 'subject' => 'msg subject', 'type' => 'notification']));
	}
	
	
	public function test_SendMail()
	{		
		$es = new EmailSupport();
		$owner = new \yii\base\Component();
		$owner->attachBehaviors(['email' => $es]);

		file_put_contents($this->getBaseFileSystem().'/emails/notification/html.php','html test');
		file_put_contents($this->getBaseFileSystem().'/emails/notification/text.php','text test');

		// disabled in config
		$this->assertFalse($owner->sendEmail(['data' => 'msg content', 'subject' => 'msg subject', 'type' => 'notification']));

		// success
		\Yii::$app->mycfg->system->email = true;
		\Yii::$app->mycfg->system->emailto = 'test@site.com';
		$this->assertTrue($owner->sendEmail(['data' => 'msg content', 'subject' => 'msg subject', 'type' => 'notification']));
	}
}
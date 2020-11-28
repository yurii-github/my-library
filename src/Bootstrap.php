<?php

namespace App;

use Slim\Factory\AppFactory;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Translation\Loader\PhpFileLoader;
use Symfony\Component\Translation\Translator;

class Bootstrap
{
    public static function initApplication()
    {
        $app = AppFactory::create();
        $app->addErrorMiddleware($_ENV['APP_DEBUG'], true, true);
        return $app;
    }
    
    public static function initDotEnv()
    {
        (new Dotenv())->load(BASE_DIR . '/.env');
    }

    public static function initTranslator()
    {
        $translator = new Translator('en_US');
        //$translator->setLocale('uk_UA');
        $translator->addLoader('php', new PhpFileLoader());
        $translator->addResource('php', SRC_DIR . '/i18n/uk_UA.php', 'uk_UA');
        return $translator;

    }
}
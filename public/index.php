<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Symfony\Component\Dotenv\Dotenv;
use \Symfony\Component\Translation\Translator;
use \Symfony\Component\Translation\Loader\PhpFileLoader;

define('BASE_DIR', dirname(__DIR__));
define('SRC_DIR', dirname(__DIR__) .'/src');

require BASE_DIR . '/vendor/autoload.php';

(new Dotenv())->load(BASE_DIR.'/.env');

$translator = new Translator('en_US');
//$translator->setLocale('uk_UA');
$translator->addLoader('php', new PhpFileLoader());
$translator->addResource('php', SRC_DIR .'/i18n/uk_UA.php', 'uk_UA');

$app = AppFactory::create();
$app->addErrorMiddleware($_ENV['APP_DEBUG'], true, true);

$app->get('/', function (Request $request, Response $response, $args) {
    $response->getBody()->write("Hello world! {$_ENV['APP_NAME']}");
    return $response;
});

$app->run();
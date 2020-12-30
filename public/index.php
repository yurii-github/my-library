<?php

use App\Configuration\Configuration;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \App\Bootstrap;
use Symfony\Component\Translation\Translator;
use Twig\Environment;

define('BASE_DIR', dirname(__DIR__));
define('DATA_DIR', dirname(__DIR__) . '/data');
define('SRC_DIR', dirname(__DIR__) . '/src');
define('WEB_DIR', dirname(__DIR__) . '/public');

require BASE_DIR . '/vendor/autoload.php';

Bootstrap::handleCliStaticData();
Bootstrap::initDotEnv();

$container = \Illuminate\Container\Container::getInstance();
$container->singleton(Configuration::class, function () {
    return new Configuration(DATA_DIR . '/config.json', '1.3');
});
$app = Bootstrap::initApplication($container);

$config = $container->get(Configuration::class);
$container->singleton(Environment::class, function () use ($config) {
    return Bootstrap::initTwig($config);
});
$twig = $container->get(Environment::class);
date_default_timezone_set($config->system->timezone);
$locale = str_replace('-', '_', $config->system->language);

$container->singleton(Translator::class, function () {
    return Bootstrap::initTranslator();
});
$translator = $container->get(Translator::class);
$translator->setLocale($locale);
$capsule = Bootstrap::initCapsule($config);


$app->get('/', \App\Actions\GetIndexPageAction::class);
$app->get('/api/book/cover', \App\Actions\GetBookCoverAction::class);
$app->get('/api/book', \App\Actions\GetBookListAction::class);
$app->get('/about', \App\Actions\GetAboutPageAction::class);


$app->run();

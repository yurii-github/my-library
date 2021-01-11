<?php

use App\Configuration\Configuration;
use \App\Bootstrap;
use Symfony\Component\Translation\Translator;
use Twig\Environment;
use \Illuminate\Container\Container;
use \App\Actions;

define('BASE_DIR', dirname(__DIR__));
define('DATA_DIR', dirname(__DIR__) . '/data');
define('SRC_DIR', dirname(__DIR__) . '/src');
define('WEB_DIR', dirname(__DIR__) . '/public');

require BASE_DIR . '/vendor/autoload.php';

Bootstrap::handleCliStaticData();
Bootstrap::initDotEnv();

$container = Container::getInstance();
$container->singleton(Configuration::class, function () {
    return new Configuration(DATA_DIR . '/config.json', '1.3');
});
$container->singleton(Environment::class, function () {
    $config = Container::getInstance()->get(Configuration::class);
    return Bootstrap::initTwig($config);
});
$container->singleton(Translator::class, function () {
    $translator = Bootstrap::initTranslator();
    $config = Container::getInstance()->get(Configuration::class);
    $locale = str_replace('-', '_', $config->system->language);
    $translator->setLocale($locale);
    return $translator;
});

$app = Bootstrap::initApplication($container);
$app->get('/', Actions\GetIndexPageAction::class);
$app->get('/api/book/cover', Actions\GetBookCoverAction::class);
$app->get('/api/book', Actions\GetBookListAction::class);
$app->post('/api/book/manage', Actions\ManageBookAction::class);
$app->post('/api/book/cover-save', Actions\UpdateBookCoverAction::class);
$app->get('/about', Actions\GetAboutPageAction::class);
$app->get('/config', Actions\GetConfigIndexAction::class);
$app->get('/config/php-info', Actions\GetPhpInfoAction::class);
$app->post('/config/save', Actions\UpdateConfigAction::class);
/*
 * config/clear-db-files?count=all
config/clear-db-files?stepping=
config/import-files
GET/POST config/import-new-cover-from-pdf
GET config/check-files
 */

$app->run();

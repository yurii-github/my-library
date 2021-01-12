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

$app->get('/', Actions\Pages\GetIndexPageAction::class);
$app->get('/api/book/cover', Actions\GetBookCoverAction::class);
$app->get('/api/book', Actions\GetBookListAction::class);
$app->post('/api/book/manage', Actions\ManageBookAction::class);
$app->post('/api/book/cover-save', Actions\UpdateBookCoverAction::class);
$app->get('/api/category', Actions\GetBookCategoryListAction::class);
$app->post('/api/category/manage', Actions\ManageBookCategoryAction::class);
$app->get('/about', Actions\Pages\GetAboutPageAction::class);
$app->get('/config', Actions\Pages\GetConfigIndexAction::class);
$app->get('/config/php-info', Actions\GetPhpInfoAction::class);
$app->post('/config/save', Actions\UpdateConfigAction::class);
$app->post('/config/vacuum', Actions\ConfigDbVacuumAction::class);
$app->get('/config/check-files', Actions\ConfigCheckFilesAction::class);
$app->get('/config/clear-db-files', Actions\ConfigClearDbFilesAction::class);
$app->get('/config/import-files', Actions\ConfigGetImportFilesAction::class);
$app->post('/config/import-files', Actions\ConfigDoImportFilesAction::class);
$app->get('/config/import-new-cover-from-pdf', Actions\ConfigGetImportNewCoverFromPdfAction::class);
$app->post('/config/import-new-cover-from-pdf', Actions\ConfigDoImportNewCoverFromPdfAction::class);

$app->run();

<?php

use \App\Bootstrap;
use \App\Actions;

define('BASE_DIR', dirname(__DIR__));
define('DATA_DIR', dirname(__DIR__) . '/data');
define('SRC_DIR', dirname(__DIR__) . '/src');
define('WEB_DIR', dirname(__DIR__) . '/public');

require BASE_DIR . '/vendor/autoload.php';

$app = Bootstrap::initApplication();

$app->get('/', Actions\Pages\IndexPageAction::class);
$app->get('/api/book/cover', Actions\GetBookCoverAction::class);
$app->get('/api/book', Actions\GetBookListAction::class);
$app->post('/api/book/manage', Actions\ManageBookAction::class);
$app->post('/api/book/cover-save', Actions\UpdateBookCoverAction::class);
$app->get('/api/category', Actions\GetBookCategoryListAction::class);
$app->post('/api/category/manage', Actions\ManageBookCategoryAction::class);
$app->get('/about', Actions\Pages\AboutPageAction::class);
$app->get('/config', Actions\Pages\ConfigIndexAction::class);
$app->get('/config/php-info', Actions\GetPhpInfoAction::class);
$app->post('/config/save', Actions\UpdateConfigAction::class);
$app->post('/config/vacuum', Actions\ConfigDbVacuumAction::class);
$app->get('/config/check-files', Actions\ConfigCheckFilesAction::class);
$app->get('/config/clear-db-files', Actions\ConfigClearDbFilesAction::class);
$app->get('/config/import-files', Actions\ConfigGetImportFilesAction::class);
$app->post('/config/import-files', Actions\ConfigDoImportFilesAction::class);
$app->get('/config/import-new-cover-from-pdf', Actions\ConfigGetImportNewCoverFromPdfAction::class);
$app->post('/config/import-new-cover-from-pdf', Actions\ConfigDoImportNewCoverFromPdfAction::class);
$app->get('/api/migrate', Actions\MigrateDatabaseAction::class);

$app->run();

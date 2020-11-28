<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \App\Bootstrap;

define('BASE_DIR', dirname(__DIR__));
define('SRC_DIR', dirname(__DIR__) .'/src');

require BASE_DIR . '/vendor/autoload.php';

Bootstrap::initDotEnv();
$translator = Bootstrap::initTranslator();
$app = Bootstrap::initApplication();

$app->get('/', function (Request $request, Response $response, $args) {
    $response->getBody()->write("Hello world! {$_ENV['APP_NAME']}");
    return $response;
});

$app->run();
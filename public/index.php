<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Symfony\Component\Dotenv\Dotenv;

define('BASE_DIR', dirname(__DIR__));
require BASE_DIR . '/vendor/autoload.php';

(new Dotenv())->load(BASE_DIR.'/.env');

$app = AppFactory::create();

$app->get('/', function (Request $request, Response $response, $args) {
    $response->getBody()->write("Hello world! {$_ENV['APP_NAME']}");
    return $response;
});

$app->run();
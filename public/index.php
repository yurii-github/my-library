<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \App\Bootstrap;

define('BASE_DIR', dirname(__DIR__));
define('DATA_DIR', dirname(__DIR__) .'/data');
define('SRC_DIR', dirname(__DIR__) .'/src');

require BASE_DIR . '/vendor/autoload.php';

Bootstrap::initDotEnv();
$translator = Bootstrap::initTranslator();
$app = Bootstrap::initApplication();
$twig = Bootstrap::initTwig();

$app->get('/', function (Request $request, Response $response, $args) use ($twig) {
    $response->getBody()->write($twig->render('about.html.twig'));
    return $response;
});

$app->run();
<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \App\Bootstrap;

define('BASE_DIR', dirname(__DIR__));
define('DATA_DIR', dirname(__DIR__) . '/data');
define('SRC_DIR', dirname(__DIR__) . '/src');
define('WEB_DIR', dirname(__DIR__) . '/public');

require BASE_DIR . '/vendor/autoload.php';

Bootstrap::handleCliStaticData();
Bootstrap::initDotEnv();
$translator = Bootstrap::initTranslator();
$app = Bootstrap::initApplication();
$twig = Bootstrap::initTwig();

$app->get('/', function (Request $request, Response $response, $args) use ($twig, $translator) {
    $uri = $request->getUri();
    $gridLocale = [
            'en_US' => 'en',
            'uk_UA' => 'ua',
    ];
    $response->getBody()->write($twig->render('about.html.twig', [
        't' => $translator,
        'path' => $uri->getPath(),
        'baseUrl' => $uri->getScheme() . '://' . $uri->getAuthority(),
        'appTheme' => $_ENV['APP_THEME'],
        'gridLocale' => $gridLocale[$translator->getLocale()],
        'projects' => [
            'Slim 4' => 'https://www.slimframework.com/',
            'jQuery' => 'https://jquery.com',
            'jQuery UI' => 'https://jqueryui.com',
            'jQuery Grid' => 'http://www.trirand.com/blog',
            'jQuery Raty' => 'http://wbotelhos.com/raty',
            'jQuery FancyBox' => 'http://fancybox.net',
            'JS-Cookie' => 'https://github.com/js-cookie/js-cookie',
            'Ghostscript' => 'https://www.ghostscript.com/'
        ]
    ]));
    return $response;
});

$app->run();

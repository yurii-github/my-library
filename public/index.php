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
$config = Bootstrap::initConfiguration();
date_default_timezone_set($config->system->timezone);
$locale = str_replace('-', '_', $config->system->language);
$translator->setLocale($locale);
$capsule = Bootstrap::initCapsule($config);

//
// ROUTES
//
$app->get('/', function (Request $request, Response $response, $args) use ($twig, $translator) {
    $uri = $request->getUri();
    $gridLocale = [
        'en_US' => 'en',
        'uk_UA' => 'ua',
    ];
    $categories = \App\Models\Category::all();
    $response->getBody()->write($twig->render('index.html.twig', [
        't' => $translator,
        'categories' => $categories,
        'path' => $uri->getPath(),
        'baseUrl' => $uri->getScheme() . '://' . $uri->getAuthority(),
        'appTheme' => $_ENV['APP_THEME'],
        'gridLocale' => $gridLocale[$translator->getLocale()],
    ]));
    return $response;
});

// Return list of books in jqgrid format
$app->get('/api/book', function (Request $request, Response $response, $args) use ($twig, $translator) {
    $filterCategories = $request->getAttribute('filterCategories');
    $columns = ['created_date', 'book_guid', 'favorite', 'read', 'year', 'title', 'isbn13', 'author', 'publisher', 'ext', 'filename'];
    $query = \App\Models\Book::query()->select($columns);

    if (!empty($filterCategories)) {
        $query->whereHas('categories', function(\Illuminate\Database\Query\Builder $query) use ($filterCategories){
            $query->whereIn('guid', explode(',', $filterCategories));
        });
    }

    $gridQuery = new \App\JGridRequestQuery($query, $request);
    $data = $gridQuery
        ->withFilters()
        ->withSorting('created_date', 'desc')
        ->paginate($columns);

    $response->getBody()->write(json_encode($data));
    return $response;
});


$app->get('/about', function (Request $request, Response $response, $args) use ($twig, $translator) {
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

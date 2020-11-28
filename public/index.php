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
$config = Bootstrap::initConfiguration();
$twig = Bootstrap::initTwig($config);
date_default_timezone_set($config->system->timezone);
$locale = str_replace('-', '_', $config->system->language);
$translator->setLocale($locale);
$capsule = Bootstrap::initCapsule($config);

//
// ROUTES
//
$app->get('/', function (Request $request, Response $response, $args) use ($config, $twig, $translator) {
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


$app->get('/api/book/cover', function (Request $request, Response $response, $args) use ($twig, $translator) {
    $book_guid = $request->getQueryParams()['book_guid'];
    $cover = \App\Models\Book::query()
        ->where('book_guid', $book_guid)
        ->first('book_cover')
        ->book_cover;
    $response = $response
        ->withHeader('Cache-Control', 'no-cache')
        ->withHeader('Content-Type', 'image/jpeg');
     $response->getBody()->write(!empty($cover) ? $cover : file_get_contents(WEB_DIR.'/assets/app/book-cover-empty.jpg'));
    return $response;
});


// Return list of books in jqgrid format
$app->get('/api/book', function (Request $request, Response $response, $args) use ($twig, $translator) {
    $params = $request->getQueryParams();
    $filterCategories = $params['filterCategories'] ?? null;
    $columns = ['created_date', 'book_guid', 'favorite', 'read', 'year', 'title', 'isbn13', 'author', 'publisher', 'ext', 'filename'];
    $query = \App\Models\Book::query()->select($columns);

    if (!empty($filterCategories)) {
        $query->whereHas('categories', function (\Illuminate\Database\Eloquent\Builder $query) use ($filterCategories) {
            $query->whereIn('guid', explode(',', $filterCategories));
        });
    }

    $gridQuery = new \App\JGridRequestQuery($query, $request);
    $gridQuery
        ->withFilters()
        ->withSorting('created_date', 'desc');;
    $response->getBody()->write(json_encode($gridQuery->paginate($columns)));
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

<?php

namespace App;

use App\Configuration\Configuration;
use Illuminate\Database\Capsule\Manager;
use Slim\Factory\AppFactory;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Translation\Loader\PhpFileLoader;
use Symfony\Component\Translation\Translator;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;

class Bootstrap
{
    public static function initConfiguration()
    {
        $config = new Configuration(DATA_DIR .'/config.json', '1.3');
        return $config;
    }
    
    
    public static function initCapsule(Configuration $config)
    {
        $capsule = new Manager();
        $capsule->addConnection([
            'driver'    => $config->database->format,
            'host'      => $config->database->host,
            'database'  => $config->database->format === 'sqlite' ? $config->database->filename : $config->database->dbname,
            'username'  => $config->database->login,
            'password'  => $config->database->password,
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
        ]);
        $capsule->setAsGlobal();
        $capsule->bootEloquent();

        $pdo = $capsule->getConnection()->getPdo();
        if ($pdo->getAttribute(\PDO::ATTR_DRIVER_NAME) === 'sqlite') {
            // not documented feature of SQLite !
            $pdo->sqliteCreateFunction('like', function ($x, $y, $escape) {
                // Example: $x = '%ч'; $y = 'bЧ'; $escape = '\';
                $x = str_replace('%', '', $x);
                $x = preg_quote($x);
                // return false;
                return preg_match('/' . $x . '/iu', $y);
            });
        }
        
        return $capsule;
    }
    
    
    public static function initApplication()
    {
        $app = AppFactory::create();
        $app->addErrorMiddleware($_ENV['APP_DEBUG'], true, true);
        return $app;
    }

    public static function initDotEnv()
    {
        (new Dotenv())->load(BASE_DIR . '/.env');
    }

    public static function initTranslator()
    {
        $translator = new Translator('en_US');
        $translator->setLocale('uk_UA');
        $translator->addLoader('php', new PhpFileLoader());
        $translator->addResource('php', SRC_DIR . '/i18n/uk_UA.php', 'uk_UA');
        return $translator;

    }

    public static function initTwig()
    {
        $loader = new FilesystemLoader(SRC_DIR . '/views');
        $twig = new Environment($loader, [
            // 'cache' => DATA_DIR . '/cache',
            //'debug' => $_ENV['APP_DEBUG'],
        ]);

        $twig->registerUndefinedFunctionCallback(function ($name) {
            if ($name === 'islinux') {
                return new TwigFunction($name, function() {
                    return strtoupper(PHP_OS) === 'LINUX';
                });
            }
            return false;
        });
        
        return $twig;
    }

    // https://stackoverflow.com/a/55090273
    public static function handleCliStaticData()
    {
        if (PHP_SAPI === 'cli-server') {
            $url = parse_url($_SERVER['REQUEST_URI']);
            $filename = WEB_DIR . $url['path'];

            // check the file types, only serve standard files
            if (preg_match('/\.(?:png|js|jpg|jpeg|gif|css)$/', $filename)) {
                if (file_exists($filename)) {
                    $fi = new \SplFileInfo($filename);
                    if ($fi->getExtension() === 'css') {
                        $mime = 'text/css';
                    } else {
                        $mime = mime_content_type($filename);
                    }
                    header('Content-Type: '.$mime);
                    readfile($filename);
                    die;
                }

                // file does not exist. return a 404
                header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
                printf('"%s" does not exist', $_SERVER['REQUEST_URI']);
                die;
            }
        }
    }
}
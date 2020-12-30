<?php
/*
 * My Book Library
 *
 * Copyright (C) 2014-2020 Yurii K.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see http://www.gnu.org/licenses
 */

namespace App;

use App\Configuration\Configuration;
use Illuminate\Database\Capsule\Manager;
use Psr\Container\ContainerInterface;
use Slim\Factory\AppFactory;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Translation\Loader\PhpFileLoader;
use Symfony\Component\Translation\Translator;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;

class Bootstrap
{
    public static function initCapsule(Configuration $config)
    {
        $capsule = new Manager();
        $capsule->addConnection([
            'driver' => $config->database->format,
            'host' => $config->database->host,
            'database' => $config->database->format === 'sqlite' ? $config->database->filename : $config->database->dbname,
            'username' => $config->database->login,
            'password' => $config->database->password,
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
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


    public static function initApplication(ContainerInterface $container)
    {
        $app = AppFactory::create(null, $container);
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

    public static function initTwig(Configuration $config)
    {
        $loader = new FilesystemLoader(SRC_DIR . '/views');
        $twig = new Environment($loader, [
            // 'cache' => DATA_DIR . '/cache',
            //'debug' => $_ENV['APP_DEBUG'],
        ]);

        $twig->registerUndefinedFunctionCallback(function ($name) use ($config) {
            if ($name === 'islinux') {
                return new TwigFunction($name, function () {
                    return strtoupper(PHP_OS) === 'LINUX';
                });
            } elseif ($name === 'copy_book_dir') {
                return new TwigFunction($name, function () use ($config) {
                    return str_replace("\\", "\\\\", $config->library->directory);
                });
            }
            return false;
        });

        //str_replace('\\', '\\\\', Yii::$app->mycfg->library->directory)

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
                    header('Content-Type: ' . $mime);
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
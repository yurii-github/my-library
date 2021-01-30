<?php
/*
 * My Book Library
 *
 * Copyright (C) 2014-2021 Yurii K.
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

namespace App\Configuration;

use App\Exception\ConfigurationDirectoryDoesNotExistException;
use App\Exception\ConfigurationDirectoryIsNotWritableException;
use App\Exception\ConfigurationFileIsNotWritableException;
use App\Exception\ConfigurationFileIsNotReadableException;
use App\Exception\ConfigurationPropertyDoesNotExistException;
use \stdClass;
use \DirectoryIterator;
use \ReflectionObject;
use \ReflectionProperty;

/**
 * @property System $system
 * @property Library $library
 * @property Database $database
 * @property Book $book
 */
final class Configuration
{
    public const SUPPORTED_VALUES = [
        'system_language' => [
            'en-US' => 'English - en-US',
            'uk-UA' => 'Українська - uk-UA'
        ],
        'system_theme' => [ // known list of JqueryUI themes
            'base',
            'black-tie',
            'blitzer',
            'cupertino',
            'dark-hive',
            'dot-luv',
            'eggplant',
            'excite-bike',
            'flick',
            'hot-sneaks',
            'humanity',
            'le-frog',
            'mint-choc',
            'overcast',
            'pepper-grinder',
            'redmond',
            'smoothness',
            'south-street',
            'start',
            'sunny',
            'swanky-purse',
            'trontastic',
            'ui-darkness',
            'ui-lightness',
            'vader'
        ],
        'system_timezone' => [
            // based on system support of DateTimeZone::listIdentifiers()
        ]

    ];

    public $version; // TODO: read only
    public $config_file; // TODO: read only
    protected $config;
    protected $options = ['system', 'database', 'library', 'book'];

    /**
     * @param string $filename database version. Increase version if database changes after release
     * @param string $version current app configuration
     * @throws ConfigurationDirectoryDoesNotExistException
     * @throws ConfigurationDirectoryIsNotWritableException
     * @throws ConfigurationFileIsNotReadableException
     * @throws ConfigurationFileIsNotWritableException
     */
    public function __construct(string $filename, string $version)
    {
        $this->version = $version;
        $this->config_file = $filename;

        if (!file_exists($this->config_file)) {
            $this->config = $this->getDefaultConfiguration();
            $this->save();
        } else {
            $this->load($this->config_file);
        }
    }

    /**
     * @param string $name
     * @throws ConfigurationPropertyDoesNotExistException
     * @return mixed
     */
    public function __get(string $name)
    {
        if (in_array($name, $this->options)) {
            return $this->config->$name;
        }

        throw new ConfigurationPropertyDoesNotExistException("Property '$name' does not exist");
    }

    /**
     * @param string $name
     * @param mixed $value
     * @throws ConfigurationPropertyDoesNotExistException
     */
    public function __set(string $name, $value)
    {
        throw new ConfigurationPropertyDoesNotExistException("Property '$name' does not exist");
    }

    // for twig
    public function getSystem()
    {
        return $this->system;
    }

    // for twig
    public function getLibrary()
    {
        return $this->library;
    }

    // for twig
    public function getDatabase()
    {
        return $this->database;
    }

    // for twig
    public function getBook()
    {
        return $this->book;
    }

    public function getLibraryBookFilenames(): array
    {
        $files = [];
        foreach (new DirectoryIterator($this->getLibrary()->directory) as $file) {
            if ($file->isFile()) {
                $files[] = $file->getFilename();
            }
        }

        return $files;
    }

    public function getFilepath(string $filename): string
    {
        return $this->getLibrary()->directory . $filename;
    }

    public function getVersion(): string
    {
        return $this->version;
    }


    /**
     * Loads configuration from JSON file.
     *
     * @param string $filename
     * @throws ConfigurationFileIsNotReadableException
     */
    protected function load(string $filename)
    {
        if (!is_readable($filename)) {
            throw new ConfigurationFileIsNotReadableException("Cannot read configuration from file '$filename'");
        }

        $this->config = json_decode(file_get_contents($filename), false);
        $this->populateNewProperties($this->config);
    }


    /**
     * Silently injects newly introduced option into current config from default config
     *
     * @param stdClass $config config to populate new properties
     */
    protected function populateNewProperties(stdClass $config)
    {
        $defaultConfig = $this->getDefaultConfiguration();
        $rf1 = new ReflectionObject($defaultConfig);
        /* @var $p_base ReflectionProperty */
        foreach ($rf1->getProperties() as $p_base) { // lvl-1: system, book ...
            $lvl1 = $p_base->name;
            if (empty($config->$lvl1)) {
                $config->$lvl1 = $defaultConfig->$lvl1;
                continue;
            }
            $rf2 = new ReflectionObject($defaultConfig->{$p_base->name});
            foreach ($rf2->getProperties() as $p_option) { //lvl-2: system->theme ..
                $lvl2 = $p_option->name;
                if (empty($config->$lvl1->$lvl2)) {
                    $config->$lvl1->$lvl2 = $defaultConfig->$lvl1->$lvl2;
                    continue; //reserved. required for lvl-3 if introduced
                }
            }
        }
    }


    protected function getDefaultConfiguration(): object
    {
        return (object)[
            'system' => (object)[
                'version' => $this->version,
                'theme' => 'smoothness',
                'timezone' => 'Europe\/Kiev',
                'language' => 'en-US'
            ],
            'library' => (object)[
                'directory' => sprintf('%s\/books\/', addslashes(DATA_DIR)),
                'sync' => false
            ],
            'database' => (object)[
                'format' => 'sqlite',
                'filename' => sprintf('%s\/mydb.s3db', addslashes(DATA_DIR)),
                'host' => 'localhost',
                'dbname' => 'mylib',
                'login' => '',
                'password' => ''
            ],
            'book' => (object)[
                'covermaxwidth' => 800,
                'covertype' => 'image\/jpeg',
                'nameformat' => '{year}, \'\'{title}\'\', {publisher} [{isbn13}].{ext}',
                'ghostscript' => ''
            ]
        ];
    }

    /**
     * @throws ConfigurationDirectoryDoesNotExistException
     * @throws ConfigurationDirectoryIsNotWritableException
     * @throws ConfigurationFileIsNotWritableException
     */
    public function save()
    {
        $filename = $this->config_file;
        $config_dir = dirname($this->config_file);

        if (file_exists($filename) && !is_writable($filename)) {
            throw new ConfigurationFileIsNotWritableException("File '$filename' is not writable");
        } elseif (is_dir($config_dir) && !is_writable($config_dir)) {
            throw new ConfigurationDirectoryIsNotWritableException("Directory '$config_dir' is not writable");
        } elseif (!is_dir($config_dir)) {
            throw new ConfigurationDirectoryDoesNotExistException("Directory '$config_dir' does not exist");
        }

        file_put_contents($filename, json_encode($this->config, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }
}





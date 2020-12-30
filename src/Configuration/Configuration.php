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

namespace App\Configuration;

/**
 * @property-read string $version
 * @property-read string $config_file
 * @property System $system
 * @property Library $library
 * @property Database $database
 * @property Book $book
 */
class Configuration
{
    const SUPPORTED_VALUES = [
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

    /** @var string Database Version. Increase version if database changes after release */
    public $version;
    /** @var string filename of config file with current app configuration */
    public $config_file;

    private $config;
    private $options = ['system', 'database', 'library', 'book'];

    private $isInstall = false;


    public function __construct($filename, $version)
    {
        $this->version = $version;
        $this->config_file = $filename;

        if (!file_exists($this->config_file)) {
            $this->saveDefaultCfg();
        } else {
            $this->load($this->config_file);
        }
    }

    public function __get($name)
    {
        if (in_array($name, $this->options)) {
            return $this->config->$name;
        }

        return $this->$name;
    }


    public function isInstall(): bool
    {
        return $this->isInstall;
    }


    /**
     * @return string
     * @deprecated
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    protected function saveDefaultCfg()
    {
        $this->config = $this->getDefaultConfiguration();
        $this->save();
    }


    public function load($filename)
    {
        if (!is_readable($filename)) {
            throw new \InvalidArgumentException('cannot read config file at this location: ' . $filename);
        }

        $this->config = json_decode(file_get_contents($filename), false);

        //
        // silently injects newly introduced option into current config from default config
        //
        $def_config = $this->getDefaultConfiguration();
        $rf1 = new \ReflectionObject($def_config);
        /* @var $p_base \ReflectionProperty */
        foreach ($rf1->getProperties() as $p_base) {// lvl-1: system, book ...
            $lvl1 = $p_base->name;
            if (empty($this->config->$lvl1)) {
                $this->config->$lvl1 = $def_config->$lvl1;
                continue;
            }
            $rf2 = new \ReflectionObject($def_config->{$p_base->name});
            foreach ($rf2->getProperties() as $p_option) {//lvl-2: system->theme ..
                $lvl2 = $p_option->name;
                if (empty($this->config->$lvl1->$lvl2)) {
                    $this->config->$lvl1->$lvl2 = $def_config->$lvl1->$lvl2;
                    continue;//reserved. required for lvl-3 if introduced
                }
            }
        }
    }


    public function getDefaultConfiguration(): object 
    {
        return (object)[
            'system' => (object)[
                'version' => $this->version,
                'theme' => 'smoothness',
                'timezone' => 'Europe\/Kiev',
                'language' => 'en-US'
            ],
            'library' => (object)[
                'codepage' => 'cp1251',
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
     * gets encoded utf-8 string in filesystem codepage type
     * @param string $filename
     * @return string
     * @deprecated 
     */
    public function Encode($filename)
    {
        if (PHP_MAJOR_VERSION >= 7) {
            return $filename;
        }

        return mb_convert_encoding($filename, $this->library->codepage, 'utf-8');
    }

    /**
     * gets utf-8 string decoded from filesystem codepage type
     *
     * @param string $filename
     * @return string
     * @deprecated
     */
    public function Decode($filename)
    {
        if (PHP_MAJOR_VERSION >= 7) {
            return $filename;
        }

        return mb_convert_encoding($filename, 'utf-8', $this->library->codepage);
    }


    public function save()
    {
        $filename = $this->config_file;
        $config_dir = dirname($this->config_file);

        if (file_exists($filename) && !is_writable($filename)) {
            throw new \InvalidArgumentException("file '$filename' is not writable", 1);
        } elseif (is_dir($config_dir) && !is_writable($config_dir)) {
            throw new \InvalidArgumentException("config directory '$config_dir' is not writable", 2);
        } elseif (!is_dir($config_dir)) {
            throw new \InvalidArgumentException("Directory does not exist", 3);
        }

        if (!file_exists($filename)) {
            $this->isInstall = true;
        }

        file_put_contents($filename, json_encode($this->config, JSON_PRETTY_PRINT));
    }
}





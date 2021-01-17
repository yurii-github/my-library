<?php

namespace App\Configuration {
    class System
    {
        /** @var string */
        public $theme;
        /** @var string */
        public $timezone;
        /** @var string */
        public $language;
        /** @var string */
        public $version;
    }

    class Library
    {
        /** @var string */
        public $directory;
        /** @var string */
        public $sync;
    }

    class Database
    {
        /** @var string */
        public $filename;
        /** @var string */
        public $dbname;
        /** @var string */
        public $format;
        /** @var string */
        public $host;
        /** @var string */
        public $login;
        /** @var string */
        public $password;
    }

    class Book
    {
        /** @var string */
        public $nameformat;
        /** @var string */
        public $covertype;
        /** @var int */
        public $covermaxwidth;
        /** @var string */
        public $ghostscript;
    }
}

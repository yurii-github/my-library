<?php
//
// config type hints support
//
namespace App\Configuration
{
    /**
     * @property string $theme
     * @property string $timezone
     * @property string $language
     * @property string $version this param is only set after successful migration install
     */
    class System {}

    /**
     * @property string $directory
     * @property string $sync
     */
    class Library {}

    /**
     * @property string $filename
     * @property string $dbname
     * @property string $format
     * @property string $host
     * @property string $login
     * @property string $password
     */
    class Database {}

    /**
     * @property string $nameformat
     * @property string $covertype
     * @property int $covermaxwidth
     * @property  string ghostscript
     */
    class Book {}
}

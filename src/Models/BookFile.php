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

namespace App\Models;

use App\Configuration\Configuration;
use Illuminate\Container\Container;

/**
 * @property-read $exists
 * @property-read $filename
 * @property-read $filepath
 */
class BookFile
{
    protected string $filename;
    protected Configuration $config;
    
    
    public function __construct(string $filename)
    {
        $this->filename = $filename;
        $this->config = Container::getInstance()->get(Configuration::class);
    }
    
    public function __get($name)
    {
        if ($name === 'exists') {
            return $this->getFileExists();
        } elseif ($name === 'filename') {
            return $this->filename;
        } elseif ($name === 'filepath') {
            return $this->getFilepath();
        }
        
        throw new \Exception("Unknown '$name' property!");
    }

    public function getFilepath(): string
    {
        return $this->config->getFilepath($this->filename);
    }
    
    public function delete(): bool
    {
        if (!$this->exists) {
            // TODO: write warning into log if file does not exist
            return false;
        }
        
        return unlink($this->filepath);
    }
    
    protected function getFileExists(): bool
    {
        if (!$this->filename) {
            return false;
        }
        
        return file_exists($this->config->getFilepath($this->filename));
    }

    public static function createForBook(Book $book): BookFile
    {
        $config = Container::getInstance()->get(Configuration::class);
        $format = $config->book->nameformat;
        $filename = str_replace(array(
            '{year}',
            '{title}',
            '{publisher}',
            '{author}',
            '{isbn13}',
            '{ext}'
        ), array(
            $book->year,
            $book->title,
            $book->publisher,
            $book->author,
            $book->isbn13,
            $book->ext
        ), $format);
        
        return new BookFile($filename);
    }
}
   
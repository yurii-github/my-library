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

class BookFile
{
    protected string $filename;
    protected string $extension;
    protected Configuration $config;
    
    
    public function __construct(string $filename)
    {
        $this->filename = $filename;
        $this->extension = pathinfo($filename, PATHINFO_EXTENSION);
        $this->config = Container::getInstance()->get(Configuration::class);
        
        if ($this->extension === null) {
            throw new \Exception("File '$filename' MUST contain extension!");
        }
    }
    
    public function getFilename(): string
    {
        return $this->filename;
    }
    
    public function getExtension(): string
    {
        return $this->extension;
    }

    public function getFilepath(): string
    {
        return $this->config->getFilepath($this->filename);
    }
    
    public function delete(): bool
    {
        if (!$this->exists()) {
            return false;
        }
        
        return unlink($this->getFilepath());
    }
    
    public function exists(): bool
    {
        return file_exists($this->getFilepath());
    }
}
   
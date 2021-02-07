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

namespace App;

use App\Exception\BookFileNotFoundException;
use App\Exception\CoverExtractionFailedException;
use App\Exception\GhostscriptIsNotConfiguredException;
use App\Exception\InvalidBookFormatException;
use Illuminate\Support\Str;

/**
 * Imports book cover from 1st page of the book.
 * Currently, it supports only PDF files.
 */
class CoverExtractor
{
    protected Configuration\Configuration $config;

    public function __construct(Configuration\Configuration $config)
    {
        $this->config = $config;
    }

    /**
     * @param string $srcPdfFile
     * @throws \Throwable
     * @return false|string|null
     */
    public function extract(string $srcPdfFile)
    {
        if (!Str::endsWith($srcPdfFile, '.pdf')) {
            throw new InvalidBookFormatException("Unsupported book format for '{$srcPdfFile}'");
        }
        if (!file_exists($srcPdfFile)) {
            throw new BookFileNotFoundException("Book file '{$srcPdfFile}' does not exist!");
        }

        $tmpFilename = tempnam(sys_get_temp_dir(), 'MYL');
        $coverData = $output = null;
        
        try {
            chmod($tmpFilename, 0777);
            $command = $this->buildGhostscriptCommand($srcPdfFile, $tmpFilename);
            $output = shell_exec($command);
            $output = str_replace("\n", '<br>', $output);
            $coverData = file_get_contents($tmpFilename);
        } finally {
            if (file_exists($tmpFilename)) {
                unlink($tmpFilename);
            }
        }

        if (empty($coverData)) {
            throw new CoverExtractionFailedException(
                "Failed to convert from <b>$srcPdfFile</b> to <b>$tmpFilename</b>.<br>" .
                "---------<br>".
                $output
            );
        }
        
        return $coverData;
    }

    /**
     * @param string $srcPdfFile
     * @param string $outJpegFile
     * @throws GhostscriptIsNotConfiguredException
     * @return string
     */
    protected function buildGhostscriptCommand(string $srcPdfFile, string $outJpegFile)
    {
        $ghostScriptEXE = $this->config->getBook()->ghostscript;
        $err2out = strtolower(PHP_OS_FAMILY) === 'linux' ? '2>&1' : '';
        
        if (!$ghostScriptEXE) {
            throw new GhostscriptIsNotConfiguredException('Ghostscript is not configured');
        }

        return <<<CMD
"$ghostScriptEXE" -dTextAlphaBits=4 -dGraphicsAlphaBits=4 -dSAFER -dNOPAUSE \
-dBATCH -dFirstPage=1 -sPageList=1 -dLastPage=1 -sDEVICE=jpeg -dJPEGQ=100 -r96 \
-sOutputFile="$outJpegFile" "$srcPdfFile" $err2out
CMD;
    }
}
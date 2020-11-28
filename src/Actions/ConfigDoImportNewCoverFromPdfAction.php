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

namespace App\Actions;

use App\Configuration\Configuration;
use App\Models\Book;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Imports book cover from book if it is PDF
 * Basically, get image from its 1st page
 */
class ConfigDoImportNewCoverFromPdfAction
{
    /**
     * @var Configuration
     */
    protected $config;


    public function __construct(ContainerInterface $container)
    {
        $this->config = $container->get(Configuration::class);
    }


    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $bookIds = Collection::make(Arr::get($request->getParsedBody(), 'post'))->pluck('book_guid');
        $addedBooks = [];
        foreach ($bookIds as $bookId) {
            try {
                $book = Book::query()->findOrFail($bookId);
                $book->book_cover = $this->extractCover($this->config->getFilepath($book->filename));
                $book->saveOrFail();
                $addedBooks[] = $book->filename;
            } catch (\Throwable $t) {
                $message = ['data' => $addedBooks, 'result' => false, 'error' => $t->getFile() . ' ' . $t->getLine() . ' ' . $t->getMessage()];
                $response->getBody()->write(json_encode($message, JSON_UNESCAPED_UNICODE));
                return $response;
            }
        }

        $message = ['data' => $addedBooks, 'result' => true, 'error' => null];
        $response->getBody()->write(json_encode($message, JSON_UNESCAPED_UNICODE));

        return $response;
    }

    /**
     * @param string $srcPdfFile
     * @throws \Throwable
     * @return false|string|null
     */
    protected function extractCover(string $srcPdfFile)
    {
        if (!file_exists($srcPdfFile)) {
            return null;
        }

        $coverData = null;
        $outJpegFile = tempnam(sys_get_temp_dir(), 'MYL');

        try {

            if (!file_exists($outJpegFile)) {
                throw new \Exception("Failed to create temporary file '$outJpegFile'");
            }
            chmod($outJpegFile, 0777);

            $command = $this->buildGhostCommand($srcPdfFile, $outJpegFile);
            $res = exec($command, $output);

            if (filesize($outJpegFile) == 0) {
                throw new \Exception("Failed to convert from <b>$srcPdfFile</b> to <b>$outJpegFile</b>. <br>ERROR: $res<br><br>" . print_r($output, true));
            }

            $coverData = file_get_contents($outJpegFile);
        } catch (\Throwable $e) {
            if (file_exists($outJpegFile)) {
                unlink($outJpegFile);
                $outJpegFile = null;
            }
            throw $e;
        }

        return $coverData;
    }

    protected function buildGhostCommand($srcPdfFile, $outJpegFile)
    {
        $ghostScriptEXE = $this->config->getBook()->ghostscript;
        return <<<CMD
"$ghostScriptEXE" \
-dTextAlphaBits=4 \
-dGraphicsAlphaBits=4 \
-dSAFER \
-dNOPAUSE \
-dBATCH \
-dFirstPage=1 \
-sPageList=1 \
-dLastPage=1 \
-sDEVICE=jpeg \
-dJPEGQ=100 \
-sOutputFile="$outJpegFile" \
-r96 \
"$srcPdfFile"
CMD;
    }

}
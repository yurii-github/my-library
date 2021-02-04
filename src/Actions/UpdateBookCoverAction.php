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

namespace App\Actions;

use App\Configuration\Configuration;
use App\Exception\InvalidImageException;
use App\Models\Book;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class UpdateBookCoverAction extends AbstractApiAction
{
    /** @var Configuration */
    protected $config;

    public function __construct(ContainerInterface $container)
    {
        $this->config = $container->get(Configuration::class);
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $params = $request->getQueryParams();

        $book = Book::where(['book_guid' => $params['book_guid'] ?? null])->firstOrFail();
        $bookCover = self::getResampledImageByWidthAsBlob((string)$request->getBody(), $this->config->book->covermaxwidth);
        $book->book_cover = $bookCover;
        $book->save();

        return $this->asJSON();
    }

    /**
     * Resamples image to match boundary limits by width. Height is not checked and will resampled according to width's change percentage.
     *
     * @param string $img_blob image source as blob string
     * @param int $max_width max allowed width for picture in pixels
     * @throws InvalidImageException
     * @return false|string
     */
    protected static function getResampledImageByWidthAsBlob(string $img_blob, $max_width = 800)
    {
        $size = getimagesizefromstring($img_blob);

        if ($size === false) {
            throw new InvalidImageException();
        }

        list($src_w, $src_h) = getimagesizefromstring($img_blob);

        $src_image = imagecreatefromstring($img_blob);
        $dst_w = $src_w > $max_width ? $max_width : $src_w;
        $dst_h = $src_w > $max_width ? ($max_width / $src_w * $src_h) : $src_h; //minimize height in percent to width
        $dst_image = imagecreatetruecolor($dst_w, $dst_h);
        imagecopyresized($dst_image, $src_image, 0, 0, 0, 0, $dst_w, $dst_h, $src_w, $src_h);
        ob_start();
        imagejpeg($dst_image);

        return ob_get_clean();
    }
}
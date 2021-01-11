<?php
/*
 * My Book Library
 *
 * Copyright (C) 2014-2019 Yurii K.
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

namespace app\controllers;

use app\Helpers\Tools;
use app\models\Books;
use app\components\Controller;
use yii\db\Exception;
use yii\db\Expression;
use yii\filters\VerbFilter;
use yii\web\Response;

class ConfigController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
               
         
                    'sync-import-new-cover-from-pdf' => ['GET', 'POST']
                ]
            ]
        ];
    }

    /**
     * Imports book cover from book if it is PDF
     * Basically, get image from its 1st page
     *
     * @return array
     */
    public function actionImportNewCoverFromPdf()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        
        if (\Yii::$app->request->getMethod() == 'GET') {
            return Books::find()->select(['book_guid'])
                ->where(new Expression('book_cover IS NULL'))
                ->andWhere(new Expression("filename LIKE '%.pdf'"))
                ->asArray()
                ->all();
        }

        return $this->POST_actionImportNewCoverFromPdf();
    }

    
    protected function POST_actionImportNewCoverFromPdf()
    {
        $bookIds = array_column(\Yii::$app->request->post('post', []), 'book_guid');
        $addedBooks = [];
        
        foreach ($bookIds as $bookId) {
            $book = Books::findOne(['book_guid' => $bookId]);
            $srcPdfFile = \Yii::$app->mycfg->library->directory . $book->filename;
            if (!file_exists($srcPdfFile)) {
                \Yii::error("file '$srcPdfFile' not exist for book '$book->book_guid'");
                continue;
            }
            
            try {
                $outJpegFile = tempnam(sys_get_temp_dir(), 'MYL');
                if (!file_exists($outJpegFile)) {
                    throw new Exception("Failed to create temporary file '$outJpegFile'");
                }
                chmod($outJpegFile, 0777);
               
                $command = $this->buildGhostCommand($srcPdfFile, $outJpegFile);
                $res = exec($command, $output);

                if (filesize($outJpegFile) == 0) {
                    throw new Exception("Failed to convert from <b>$srcPdfFile</b> to <b>$outJpegFile</b>. <br>ERROR: $res<br><br>" . print_r($output, true));
                }

                $book->book_cover = file_get_contents($outJpegFile);
                if (!$book->save()) {
                    throw new Exception("Failed to save book cover for book '$book->book_guid'");
                }

                $addedBooks[] = $book->filename;
            }
            catch (\Throwable $t) {
                return ['data' => $addedBooks, 'result' => false, 'error' => $t->getMessage()];
            }
            finally {
                unlink($outJpegFile);
            }
        }

        return ['data' => $addedBooks, 'result' => true, 'error' => null];
    }
    
    
    protected function buildGhostCommand($srcPdfFile, $outJpegFile)
    {
        $ghostScriptEXE = \Yii::$app->mycfg->book->ghostscript;
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

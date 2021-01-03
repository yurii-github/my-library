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
                    'index' => ['GET'],
                    'save' => ['POST'],
                    'vacuum' => ['POST'],
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

    /**
     * returns array of books filenames located in FS library folder
     * filename is in UTF-8
     */
    private function getLibraryBookFilenames()
    {
        $files = [];
        try {
            $libDir = new \DirectoryIterator(\Yii::$app->mycfg->library->directory);
            foreach ($libDir as $file) {
                if ($file->isFile()) {
                    $files[] = \Yii::$app->mycfg->Decode($file->getFilename());
                }
            }
        } finally {//suppress any errors
            if (!is_array($files)) {
                $files = [];
            }
        }
        return $files;
    }

    public function actionIndex()
    {
        return $this->render('index');
    }


    public function actionVacuum()
    {
        $result = Tools::compact(\Yii::$app->mycfg);

        $msgString = implode("\n", array_filter([
            $result[0] ? "Type: $result[0]" : null,
            $result[1] ? "ERROR: $result[1]" : null,
            $result[2] ? "Old size: $result[2]" : null,
            $result[3] ? "New size: $result[3]" : null,
        ]));

        return $msgString;
    }

    public function actionCheckFiles()
    {
        // TODO: read with iterator, not all. may use too much memory
        $files_db = [];
        foreach (Books::find()->select(['filename'])->all() as $book) {
            $files_db[] = $book['filename'];
        }

        $files = $this->getLibraryBookFilenames();
        $arr_db_only = array_diff($files_db, $files);
        $arr_fs_only = array_diff($files, $files_db);

        return json_encode(array(
            'db' => array_values($arr_db_only),
            'fs' => array_values($arr_fs_only)
        ), JSON_UNESCAPED_UNICODE);
    }

    public function actionImportFiles()
    {
        if (\Yii::$app->request->getMethod() == 'GET') {
            return json_encode($this->getFiles_FileSystemOnly(), JSON_UNESCAPED_UNICODE);
        }

        if (\Yii::$app->request->getMethod() == 'POST') {
            $error = '';
            $post = \Yii::$app->request->post('post', []);

            $arr_added = [];
            try {
                foreach ($post as $f) {
                    $book = new Books(['scenario' => 'import']);
                    $book->filename = $book->title = $f;
                    $book->insert();
                    $arr_added[] = $f;
                }
            } catch (\Exception $e) {
                return json_encode(['data' => $arr_added, 'result' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
            }

            return json_encode(['data' => $arr_added, 'result' => true, 'error' => ''], JSON_UNESCAPED_UNICODE);
        }
    }

    protected function getFiles_FileSystemOnly()
    {
        // TODO: read with iterator, not all. may use too much memory
        $files_db = [];
        $books = Books::find()->select(['filename'])->asArray()->all();

        foreach ($books as $book) {
            $files_db[] = $book['filename'];
        }
        $files = $this->getLibraryBookFilenames();
        $arr_fs_only = array_values(array_diff($files, $files_db));
        return $arr_fs_only;
    }

    public function actionClearDbFiles()
    {
        //count number of records to clean
        if (\Yii::$app->request->get('count') == 'all') {
            $counter = 0;
            foreach (Books::find()->select(['book_guid', 'filename'])->each() as $r) {
                $file =
                    \Yii::$app->mycfg->Encode(\Yii::$app->mycfg->library->directory . '/' . $r->filename);
                if (!file_exists($file)) {
                    $counter++;
                }
            }
            return $counter;
        }

        //else clean records in stepping/waves
        $stepping = \Yii::$app->request->get('stepping', 5); //records to delete in 1 wave
        $data = [];
        $counter = 0;
        foreach (Books::find()->select(['book_guid', 'filename'])->each() as $r) {
            if ($counter >= $stepping) break;
            $file = \Yii::$app->mycfg->Encode(\Yii::$app->mycfg->library->directory . '/' . $r->filename);
            if (!file_exists($file)) {
                Books::deleteAll(['book_guid' => $r->book_guid]);
                $data[] = $r->book_guid;
                $counter++;
            }
        }
        //sleep(1);
        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    public function actionSave()
    {
        $resp = new \stdClass();
        $resp->msg = '';
        $resp->result = false;
        $resp->title = '';

        $field = \Yii::$app->request->post('field');
        $value = \Yii::$app->request->post('value');

        list($group, $attr) = explode('_', $field);

        try {
            \Yii::$app->mycfg->$group->$attr = $value;
            \Yii::$app->mycfg->save();
            $resp->msg = "<b>$attr</b> was successfully updated";
            $resp->result = true;
        } catch (\Exception $e) {
            $resp->msg = __FILE__ . ': ' . __LINE__ . ' ' . $e->getMessage();
            $resp->result = false;
        } finally {
            $resp->title = $group;
        }

        return json_encode($resp);
    }

    public function actionPhpInfo()
    {
        return $this->renderPartial('phpinfo');
    }

}

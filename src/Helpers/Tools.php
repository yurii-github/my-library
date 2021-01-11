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

namespace App\Helpers;

use app\components\Configuration;
use yii\base\NotSupportedException;

class Tools
{
    /**
     * resamples image to match boundary limits by width. Height is not checked and will resampled according to width's change percentage
     *
     * @param string $img_blob image source as blob string
     * @param int $max_width max allowed width for picture in pixels
     *
     * @return string image as string BLOB
     */
    static public function getResampledImageByWidthAsBlob($img_blob, $max_width = 800)
    {
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
    
    
    /**
     * Compact database
     *
     * @param Configuration $cfg
     * @throws NotSupportedException
     * @throws \yii\db\Exception
     * @return array [type, error, oldSize, new Size]
     */
    static public function compact(Configuration $cfg)
    {
        \Yii::$app->db->open();
        $driver = \Yii::$app->db->getDriverName();
        $msg = null;

        switch ($driver) {
            case 'sqlite':
                $msg = self::compactSqlite($cfg, \Yii::$app->db->pdo);
                break;
            case 'mysql':
                $msg = self::compactMysql($cfg, \Yii::$app->db->pdo);
                break;
            default:
                throw new NotSupportedException("DB Driver '$driver' is not supported yet!");
        }

        return $msg;
    }

    /**
     * @param Configuration $cfg
     * @param \PDO $pdo
     * @return array [type, error, oldSize, new Size]
     */
    static protected function compactMysql(Configuration $cfg, \PDO $pdo)
    {
        try {
            $querySize = <<<SQL
SELECT SQL_NO_CACHE SUM(DATA_LENGTH + INDEX_LENGTH) FROM information_schema.TABLES 
WHERE table_schema = :dbname
GROUP BY table_schema
SQL;

            $sSize = $pdo->prepare($querySize);
            $sSize->bindValue(':dbname', $cfg->database->dbname);
            $sSize->execute();
            $oldSize = $sSize->fetchColumn();
            $tables = $pdo->query("SHOW TABLES")->fetchAll(\PDO::FETCH_COLUMN);
            $tables = implode(',', $tables);
            $pdo->query("OPTIMIZE TABLE $tables");
            $sSize->execute();
            $newSize = $sSize->fetchColumn();
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }

        return ['MYSQL OPTIMIZE', $error ?? '', $oldSize ?? null, $newSize ?? null];
    }


    /**
     * @param Configuration $cfg
     * @param \PDO $pdo
     * @return array [type, error, oldSize, new Size]
     */
    static protected function compactSqlite(Configuration $cfg, \PDO $pdo)
    {
        try {
            $filename = $cfg->database->filename;
            $pdo->query("VACUUM");
            $oldSize = (new \SplFileInfo($filename))->getSize();
            clearstatcache(true, $filename); // we need new size, not old one
            $newSize = (new \SplFileInfo($filename))->getSize();
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }

        return ['SQLITE VACUUM', $error ?? '', $oldSize ?? null, $newSize ?? null];
    }


    /**
     * generates global unique id
     *
     * format: hhhhhhhh-hhhh-hhhh-hhhh-hhhhhhhhhhhh
     *
     * @return string GUID
     */
    static public function com_create_guid()
    {
        mt_srand((double)microtime() * 10000);
        $charid = strtoupper(md5(uniqid(rand(), true)));
        return substr($charid, 0, 8) . '-' . substr($charid, 8, 4) . '-' . substr($charid, 12, 4) . '-' . substr($charid, 16, 4) . '-' . substr($charid, 20, 12);
    }


    /**
     * parse phpinfo into array
     *
     * @param boolean $return TRUE return as array, print otherwise
     * @return mixed array or void
     *
     * @see source from http://www.php.net/manual/en/function.phpinfo.php#87463
     */
    static public function getPhpInfo()
    {
        $phpinfo_array = function ($return = false) {
            ob_start();
            phpinfo(INFO_ALL);

            $pi = preg_replace(
                array('#^.*<body>(.*)</body>.*$#ms', '#<h2>PHP License</h2>.*$#ms',
                    '#<h1>Configuration</h1>#', "#\r?\n#", "#</(h1|h2|h3|tr)>#", '# +<#',
                    "#[ \t]+#", '#&nbsp;#', '#  +#', '# class=".*?"#', '%&#039;%',
                    '#<tr>(?:.*?)" src="(?:.*?)=(.*?)" alt="PHP Logo" /></a>'
                    . '<h1>PHP Version (.*?)</h1>(?:\n+?)</td></tr>#',
                    '#<h1><a href="(?:.*?)\?=(.*?)">PHP Credits</a></h1>#',
                    '#<tr>(?:.*?)" src="(?:.*?)=(.*?)"(?:.*?)Zend Engine (.*?),(?:.*?)</tr>#',
                    "# +#", '#<tr>#', '#</tr>#'),
                array('$1', '', '', '', '</$1>' . "\n", '<', ' ', ' ', ' ', '', ' ',
                    '<h2>PHP Configuration</h2>' . "\n" . '<tr><td>PHP Version</td><td>$2</td></tr>' .
                    "\n" . '<tr><td>PHP Egg</td><td>$1</td></tr>',
                    '<tr><td>PHP Credits Egg</td><td>$1</td></tr>',
                    '<tr><td>Zend Engine</td><td>$2</td></tr>' . "\n" .
                    '<tr><td>Zend Egg</td><td>$1</td></tr>', ' ', '%S%', '%E%'),
                ob_get_clean());

            $sections = explode('<h2>', strip_tags($pi, '<h2><th><td>'));
            unset($sections[0]);

            $pi = array();
            foreach ($sections as $section) {
                $n = substr($section, 0, strpos($section, '</h2>'));
                preg_match_all(
                    '#%S%(?:<td>(.*?)</td>)?(?:<td>(.*?)</td>)?(?:<td>(.*?)</td>)?%E%#',
                    $section, $askapache, PREG_SET_ORDER);
                foreach ($askapache as $m)
                    $pi[$n][$m[1]] = (!isset($m[3]) || $m[2] == $m[3]) ?
                        @$m[2] : array_slice($m, 2); // my fix
            }

            return ($return === false) ? print_r($pi) : $pi;
        };

        return $phpinfo_array(true);
    }
}

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

namespace app\helpers;

use app\components\Configuration;
use yii\base\NotSupportedException;

class Tools
{
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


}

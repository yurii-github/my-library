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
use Illuminate\Database\Capsule\Manager;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Compact database
 */
class ConfigDbVacuumAction
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
        $connection = Manager::connection();
        $driver = $connection->getDriverName();

        if ($driver === 'sqlite') {
            $message = self::compactSqlite($this->config, $connection->getPdo());
        } elseif ($driver === 'mysql') {
            $message = self::compactMysql($this->config, $connection->getPdo());
        } else {
            throw new NotSupportedException("DB Driver '$driver' is not supported yet!");
        }

        $msgString = implode("\n", array_filter([
            $message[0] ? "Type: $message[0]" : null,
            $message[1] ? "ERROR: $message[1]" : null,
            $message[2] ? "Old size: $message[2]" : null,
            $message[3] ? "New size: $message[3]" : null,
        ]));

        $response->getBody()->write($msgString);

        return $response;
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
     * @param Configuration $cfg
     * @param \PDO $pdo
     * @return array [type, error, oldSize, new Size]
     */
    static protected function compactMysql(Configuration $cfg, \PDO $pdo)
    {
        try {
            $querySize = <<<TXT
SELECT SQL_NO_CACHE SUM(DATA_LENGTH + INDEX_LENGTH) FROM information_schema.TABLES 
WHERE table_schema = :dbname
GROUP BY table_schema
TXT;

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

}
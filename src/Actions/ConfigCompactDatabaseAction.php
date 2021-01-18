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
use Illuminate\Database\Capsule\Manager;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use \PDO;
use \SplFileInfo;

class ConfigCompactDatabaseAction
{
    /** @var Configuration */
    protected $config;
    /** @var Manager */
    protected $db;

    public function __construct(ContainerInterface $container)
    {
        $this->config = $container->get(Configuration::class);
        $this->db = $container->get('db');
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $connection = $this->db->getConnection();
        $driver = $connection->getDriverName();
        $message = "DB Driver '$driver' is not supported yet!";

        if ($driver === 'sqlite') {
            $message = $this->compactSqlite($this->config->database->filename, $connection->getPdo());
        } elseif ($driver === 'mysql') {
            $message = self::compactMysql($this->config->database->dbname, $connection->getPdo());
        }

        $response->getBody()->write($message);

        return $response;
    }

    protected function compactSqlite(string $filename, PDO $pdo)
    {
        if ($filename === ':memory:') {
            return "SQLITE COMPACT\n\nSkipping. Cannot compact in-memory database\n";
        }

        $oldSize = (new SplFileInfo($filename))->getSize();
        $pdo->query("VACUUM");
        clearstatcache(true, $filename);
        $newSize = (new SplFileInfo($filename))->getSize();

        return "SQLITE COMPACT\n\nOld size: {$oldSize}\nNew size: {$newSize}\n";
    }

    protected function compactMysql(string $dbName, \PDO $pdo)
    {
        $querySize = <<<TXT
SELECT SQL_NO_CACHE SUM(DATA_LENGTH + INDEX_LENGTH) FROM information_schema.TABLES 
WHERE table_schema = :dbname
GROUP BY table_schema
TXT;
        $sSize = $pdo->prepare($querySize);
        $sSize->bindValue(':dbname', $dbName);
        $sSize->execute();
        $oldSize = $sSize->fetchColumn();
        $tables = $pdo->query("SHOW TABLES")->fetchAll(\PDO::FETCH_COLUMN);
        $tables = implode(',', $tables);
        $pdo->query("OPTIMIZE TABLE $tables");
        $sSize->execute();
        $newSize = $sSize->fetchColumn();

        return "MYSQL COMPACT\n\nOld size: {$oldSize}\nNew size: {$newSize}\n";
    }

}
<?php

namespace tests;

trait DbTrait
{
    private static $pdo;

    protected $dataset = [];

    /**
     *
     * @return \PDO
     */
    protected function getPdo()
    {
        if (empty(self::$pdo)) {
            $env_db = getenv('DB_TYPE');
            $db = $GLOBALS['db'][$env_db];
            self::$pdo = new \PDO($db['dsn'], @$db['username'], @$db['password'], [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);

            foreach (explode(';', file_get_contents(self::$baseTestDir . "/data/db.{$env_db}.txt")) as $query) {
                if (!empty(trim($query))) {
                    self::$pdo->query(trim($query));
                }
            }
        }

        return self::$pdo;
    }

    /**
     *  remove tables, fully clean database
     */
    protected function cleanDb()
    {
        foreach (['books', 'categories', 'books_categories'] as $tbname) {
            $sql = "DROP TABLE IF EXISTS $tbname";
            self::getPdo()->query($sql);
        }
    }

    protected function resetConnection()
    {
        self::$pdo = null; // reset pdo connection
    }

    protected function getFixture($name)
    {
        return require self::$baseTestDir . "/data/fixtures/$name.php";
    }

    protected function loadFixtures()
    {
        foreach ($this->dataset as $table => $rows) {
            foreach ($rows as $row) {
                $columns = array_keys($row);
                foreach ($columns as &$column) {
                    $column = "`$column`";
                }
                $arrColumns = implode(',', $columns);
                $values = array_values($row);
                $count = count($columns);
                $arrCount = [];
                for ($i=0; $i < $count; $i++) {
                    $arrCount[] = '?';
                }
                $arrCount = implode(',', $arrCount);
                $query = "INSERT INTO $table ($arrColumns) VALUES($arrCount)";
                //var_dump($query); die;
                $stmt = $this->getPdo()->prepare($query);
                if (empty($values['0'])) {
                //    var_dump($values); die;
                }
               //
                for ($i=0; $i < $count; $i++) {
                    $stmt->bindValue($i+1, $values[$i]);
                }
                $stmt->execute();
            }
        }
    }


    /**
     *
     * @param string $name table/fixture name
     * @return unknown
     */
    protected function setupFixture($name)
    {
        $fixture = $this->getFixture($name);


        $this->dataset = [
            $name => $fixture['inserted']
        ];
      //  var_dump($this->dataset); die;
        return $fixture;
    }
}

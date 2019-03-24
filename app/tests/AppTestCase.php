<?php

namespace tests {

    use app\components\Configuration;
    use org\bovigo\vfs\vfsStream;
    use PHPUnit\Framework\TestCase;

    class AppTestCase extends TestCase
    {
        protected static $baseTestDir = __DIR__;
        private static $pdo;    // pdo connection
        private static $dbc;    // dbunit connection, contains pdo

        protected $dataset = [ //for clearing with dbunit via truncate
            'books' => [],
            'users' => []
        ];

        private $is_fs_init = false;


        function __destruct()
        {
            $this->destroyApplication();
        }

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

                //init tables
                foreach (explode(';', file_get_contents(self::$baseTestDir . "/data/db.sqlite_mysql.txt")) as $query) {

                    if (!empty($query)) {
                        if ($env_db == 'mysql' && preg_match('/CREATE TABLE/i', $query)) {
                            $query .= ' ENGINE=InnoDB DEFAULT CHARSET=utf8'; // for mysql
                        }
                        self::$pdo->query($query);
                        //var_dump($query);
                    }
                }
            }

            return self::$pdo;
        }


        /**
         * (non-PHPdoc)
         * @see PHPUnit_Extensions_Database_TestCase::getConnection()
         */
        protected function getConnection()
        {
            if (empty(self::$dbc)) {
                self::$dbc = $this->createDefaultDBConnection($this->getPdo());
            }
            return self::$dbc;
        }


        /**
         * (non-PHPdoc)
         * @see PHPUnit_Extensions_Database_TestCase::getDataSet()
         */
        public function getDataSet()
        {
            return $this->createArrayDataSet($this->dataset);
        }


        /**
         * (non-PHPdoc)
         * @see PHPUnit_Extensions_Database_TestCase::tearDown()
         */
        protected function tearDown(): void
        {
            $this->destroyApplication();
            parent::tearDown();

        }


        /**
         * @param array $config
         * @throws \yii\base\InvalidConfigException
         */
        protected function mockYiiApplication(array $config = [])
        {
            $this->initAppFileSystem();

            $env_db = getenv('DB_TYPE');
            $db = $GLOBALS['db'][$env_db];

            new \yii\web\Application(\yii\helpers\ArrayHelper::merge([
                'id' => 'testapp',
                'basePath' => vfsStream::url('base'),
                'vendorPath' => dirname(dirname(self::$baseTestDir)) . '/vendor',
                'aliases' => [
                    '@runtime' => '@app/runtime',
                    '@webroot' => '@app/public',
                ],
                'components' => [
                    //'basePath' => \Yii::getAlias('@app/public/assets')
                    'i18n' => [
                        'translations' => [
                            'frontend/*' => [
                                'class' => \yii\i18n\PhpMessageSource::class,
                                'basePath' => $GLOBALS['basedir'] . '/i18n',
                                'sourceLanguage' => 'en-US'
                            ]
                        ],
                    ],
                    'db' => [
                        'class' => \yii\db\Connection::class,
                        'dsn' => $db['dsn'],
                        'username' => @$db['username'],
                        'password' => @$db['password'],
                        'pdo' => $this->getPdo()
                    ],
                    'request' => [
                        'cookieValidationKey' => 'key',
                        'scriptFile' => __DIR__ . '/index.php',
                        'scriptUrl' => '/index.php',
                    ],
                    'mycfg' => [
                        'class' => \app\components\Configuration::class,
                        'config_file' => $this->getConfigFilename()
                    ]
                ]
            ], $config));

        }


        /**
         *
         */
        protected function destroyApplication()
        {
            \Yii::$app = null;
        }

        private function getFi2xture($name): string
        {
            return require self::$baseTestDir . "/data/fixtures/$name.php";
        }

        /**
         *
         * @param string $name table/fixture name
         * @return unknown
         */
        protected function setupFi2xture($name)
        {
            $fixture = $this->getFixture($name);

            $this->dataset = [
                $name => $fixture['inserted']
            ];


            return $fixture;
        }


        // - - - - - - FS - - - - >
        protected function getConfigFilename()
        {
            return \Yii::getAlias('@app/config/libconfig.json');
        }

        protected function initAppFileSystem()
        {
            if ($this->is_fs_init) {
                return vfsStream::url('base');
            }

            vfsStream::setup('base', null, [
                'config' => [],
                'data' => [
                    'books' => []
                ],
                'emails' => [
                    'layouts' => [],
                    'notification' => []
                ],
                'runtime' => [
                    'logs' => [],
                    'mail' => []
                ],
                'public' => [
                    'assets' => [
                        'app' => []
                    ]

                ]
            ]);

            \Yii::$aliases['@app'] = vfsStream::url('base');
            //\Yii::$aliases['@webroot'] = vfsStream::url('base/public');

            $this->is_fs_init = true;

            return vfsStream::url('base');
        }

        // < - - - - - - FS - - - - -

        /**
         *  remove tables, fully clean database
         */
        function cleanDb()
        {
            foreach (array_keys($this->dataset) as $tbname) {
                $sql = "DROP TABLE IF EXISTS $tbname";
                self::getPdo()->query($sql);
            }
        }


        protected function resetConnection()
        {
            self::$pdo = self::$dbc = null; // reset pdo connection
        }

    }

}

namespace yii\base {
    //vsFS fix in MOdule
    function realpath($path)
    {
        return $path;
    }
}


namespace app\models {
    //do not send header during tests
    function header($s)
    {
        return $s;
    }
}

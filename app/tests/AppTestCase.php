<?php

namespace tests {

    use org\bovigo\vfs\vfsStream;
    use PHPUnit\Framework\TestCase;

    class AppTestCase extends TestCase
    {
        use DbTrait;

        protected static $baseTestDir = __DIR__;
        private $is_fs_init = false;

        public function __destruct()
        {
            $this->destroyApplication();

            if ($this->getPdo()->inTransaction()) {
                $this->getPdo()->rollBack();
            }
        }

        protected function setUp(): void
        {
            $this->getPdo()->beginTransaction();
            $this->loadFixtures();

            parent::setUp();
        }

        protected function tearDown(): void
        {
            $this->destroyApplication();

            if ($this->getPdo()->inTransaction()) {
                $this->getPdo()->rollBack();
            }

            parent::tearDown();
        }

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
                    '@data'  => '@app/data',
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
                        'config_file' => $this->getConfigFilename(),
                        'version' => '1.3',
                    ],
                ]
            ], $config));

        }

        protected function destroyApplication()
        {
            \Yii::$app = null;
        }


        // - - - - - - FS - - - - >
        protected function getConfigFilename()
        {
            return \Yii::getAlias('@data/config.json');
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
            \Yii::$aliases['@data'] = vfsStream::url('base/data');
            //\Yii::$aliases['@webroot'] = vfsStream::url('base/public');

            file_put_contents(\Yii::getAlias('@data/config.json'), file_get_contents(self::$baseTestDir.'/data/default_config.json'));

            $this->is_fs_init = true;

            return vfsStream::url('base');
        }

        // < - - - - - - FS - - - - -
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

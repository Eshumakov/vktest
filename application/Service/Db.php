<?php

class Service_Db {

    protected static $_instance = null;

    protected $dbh;

    /*
     * $dbConfig = array(
        'user' => (string),
        'db' => (string),
        'pass' => (string),
        'host' => (string),
        'port' => (int)
       );
     */
    private function __construct($dbConf)
    {
        if (empty($dbConf)) {
            $dbConf = Base_Config::getDbConfig();
        }
        if (isset(
                $dbConf['user'],
                $dbConf['db'],
                $dbConf['pass'],
                $dbConf['host'],
                $dbConf['port']
            ))
        {
            $this->dbh = new PDO('mysql:host=' . $dbConf['host'] . ';dbname=' . $dbConf['db'], $dbConf['user'], $dbConf['pass']);
        } else {
            throw new Exception('wrong database config');
        }
    }

    public static function i($dbConf = false)
    {
        if (empty(self::$_instance)) {
            self::$_instance = new Service_Db($dbConf);
        }

        return self::$_instance;
    }

    /*
     * @return PDO
     */
    public function dbh()
    {
        return $this->dbh;
    }

    public static function toWin1251(&$data, $deph = 5, $size = 1000)
    {

        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    self::toWin1251($value, $deph, $size);
                    $data[$key] = $value;
                } else {
                    $data[$key] = iconv('utf-8', 'windows-1251', $value);
                }
            }
        } else {
            $data = iconv('utf-8', 'windows-1251', $data);
        }
    }

    public static function fromUtfRecursive(&$data, $deph = 5, $size = 1000)
    {

        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    self::fromUtfRecursive($value, $deph, $size);
                    $data[$key] = $value;
                } else {
                    $data[$key] = utf8_decode($value);
                }
            }
        } else {
            utf8_decode($data);
        }
    }

}
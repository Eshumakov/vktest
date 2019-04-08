<?php

class Base_Config
{
    public static function getUploadImageLink()
    {
        return '/admin/index/easyUpload/';
    }

    public static function getRtfDirectory()
    {
        return '/share/rtf/';
    }

    public static function getUploadRtfLink()
    {
        return '/admin/index/saveTextFile/';
    }

    public static function getDbConfig()
    {
        return array(
            'user' => 'root',
            'db' => 'vktest',
            'pass' => 'senf15',
            'host' => 'localhost',
            'port' => 3306
        );
    }
    
    public static function getRoutes()
    {
        return array_reverse([
            '^/$' => ['Feed','Index','indexAction'],

        ]);
    }

    public function getDb()
    {
        $dbConfig = $this->getDbConfig();
        return new PDO('mysql:host=' . $dbConfig['host'] . ';dbname=' . $dbConfig['db'], $dbConfig['user'], $dbConfig['pass']);
    }

    public static function getMqConfig()
    {
        return array(
            'host' => 'localhost',
            'port' => 5672,
            'vhost' => '/',
            'login' => 'guest',
            'password' => 'guest'
        );
    }

    public static function getSiteDir()
    {
       
        return '/home/a/abagab/test.internet-akademia.ru';
    }

}
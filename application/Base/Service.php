<?php
class Base_Service
{
    const STORE_HOUR = 3600;
    const STORE_DAY = 86400;

    public static function getIP() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }

    public static function getCookie($name)
    {
        if (!empty($_COOKIE[$name])) {
            return $_COOKIE[$name];
        }

        return null;
    }
}
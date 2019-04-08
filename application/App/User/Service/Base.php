<?php

class User_Service_Base
{
    const COOKIE_UID = 'uid';
    const COOKIE_UH = 'uhash';

    const USER_SALT = '548e475d82379376995eb71de6caa17e';

    /**
     * @return bool
     */
    public static function getUser()
    {
        $uid = Base_Service::getCookie('uid');
        $uhash = Base_Service::getCookie('uhash');

        if (!$uid || !$uhash) {
            return false;
        }

        $user = (new User_Dao_Base())->getUserById($uid);

        if (!$user) {
            return false;
        }

        $hash = self::makeLoginHash($user['id'], $user['password'], Base_Service::getIP());

        if ($hash != $uhash) {
            return false;
        }

        return new Base_Model_User($user);
    }

    /**
     * @param $user
     */
    public static function makeLoginHash($userId, $password, $ip)
    {
        $hash = self::makeHash($userId, $password, $ip);
        return $hash;
    }

    protected static function makeHash($id, $password, $ip)
    {
        return md5($id . $password . $ip);
    }
}
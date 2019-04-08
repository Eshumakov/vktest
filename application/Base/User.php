<?php
class Base_User
{
    const TABLE = 'users';
    const SALT = 'H3xFeLsopnS34I';

    public function getUser()
    {

        $userId = (int) (!empty($_COOKIE['user_id']) ? $_COOKIE['user_id'] : 0);
        $hw = !empty($_COOKIE['hw']) ? $_COOKIE['hw'] : '';

        if (!$userId || !$hw) {
            return false;
        }

        $user = $this->getUserById($userId);
        if (!$user) {
            return false;
        }

        $realHw = $this->getHw($user);
        if ($realHw !== $hw) {
            return false;
        }

        return $user;
    }

    public function checkUser($login, $password, $ttl = 3600)
    {
        if (!$login || !$password) {
            return false;
        }

        $user = $this->getUserByLogin($login);

        if (!$user) {
            return false;
        }

        $password = md5($password);
        if ($password != $user->getPassword()) {
            return false;
        }

        $hw = $this->getHw($user);
        setcookie('user_id', $user->getId(), (TIME + $ttl), '/');
        setcookie('hw', $hw, (TIME + $ttl), '/');
        return $user;
    }

    public function getUserById($id)
    {
        $db = Service_Db::i()->dbh();
        $select = $db->prepare('select * from ' . self::TABLE . ' where user_id = ?');
        if (!$select->execute([$id])) {
            $select->errorInfo();
        };

        $user = $select->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return false;
        }

        return new Base_Model_User($user);
    }

    public function getUserByLogin($login)
    {
        $db = Service_Db::i()->dbh();
        $select = $db->prepare('select * from ' . self::TABLE . ' where name = ?');
        if (!$select->execute([$login])) {
            $select->errorInfo();
        };

        $user = $select->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return false;
        }


        return new Base_Model_User($user);
    }

    /**
     * @param Base_Model_User $user
     */
    public function getHw($user)
    {
        $str = '';
        $browser = crc32($_SERVER['HTTP_USER_AGENT']);
        $str = $user->getId() . $user->getLogin() . $browser . self::SALT;
        $str = md5($str);
        $str = substr($str, 0, 16);

        return $str;
    }
}
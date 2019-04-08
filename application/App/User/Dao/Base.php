<?php
class User_Dao_Base
{
    const TABLE = 'users';

    const MC_USER = 'mc_user:';
    const MC_USER_LOGIN = 'mc_user_login:';

    public function getUserById($userId)
    {
        $mcKey = self::MC_USER . $userId;
        $res = Base_Memcache::i()->get($mcKey);
        if ($res === false) {
            $res = Service_Db::i()->dbh()->prepare("SELECT * FROM " . self::TABLE . " WHERE id = ?");
            $res->execute([(int) $userId]);
            $res = $res->fetch(PDO::FETCH_ASSOC);
            Base_Memcache::i()->set($mcKey, $res);
        }

        return $res;
    }

    public function getMultiple($ids)
    {
        $ids = array_unique($ids);
        $in  = str_repeat('?,', count($ids) - 1) . '?';
        $res = Service_Db::i()->dbh()->prepare("SELECT * FROM " . self::TABLE . " WHERE id in ($in)");
        $res->execute($ids);
        return $res->fetchAll(PDO::FETCH_UNIQUE);
    }

    public function getByLogin($login)
    {
        $mcKey = self::MC_USER_LOGIN . $login;
        $res = Base_Memcache::i()->get($mcKey);
        $res = false;
        if ($res === false) {
            $res = Service_Db::i()->dbh()->prepare("SELECT * FROM " . self::TABLE . " WHERE login = ?");
            $res->execute([$login]);
            $res = $res->fetch(PDO::FETCH_ASSOC);
            Base_Memcache::i()->set($mcKey, $res);
        }

        return $res;
    }
}
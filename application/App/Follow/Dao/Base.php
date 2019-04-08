<?php
class Follow_Dao_Base
{
    const TABLE = 'follows';
    const MC_KEY = 'follow_user:';

    const MAX_FOLLOW_COUNT = 1000;

    public function getUserById($userId)
    {
        $mcKey = self::MC_KEY . $userId;
        $res = Base_Memcache::i()->get($mcKey);
        $res = false;
        if ($res === false) {
            $sql = "SELECT * FROM " . self::TABLE . " WHERE user_id = ?";
            $res = Service_Db::i()->dbh()->prepare($sql);
            $params = [(int) $userId];
            $res->execute($params);
            $res = $res->fetchAll(PDO::FETCH_ASSOC);
            $res = array_column($res, 'author_id');
            Base_Memcache::i()->set($mcKey, $res);
        }

        return $res;
    }

    public function add($followerId, $authorId)
    {
        $mcKey = self::MC_KEY . $followerId;
        $sql = "INSERT INTO " . self::TABLE . " (`user_id`, `author_id`) VALUES (?, ?)";
        $res = Service_Db::i()->dbh()->prepare($sql);
        $res = $res->execute([$followerId, $authorId]);
        Base_Memcache::i()->delete($mcKey);
        return $res;
    }

    public function delete($followerId, $authorId)
    {
        $mcKey = self::MC_KEY . $followerId;
        $sql = "DELETE FROM " . self::TABLE . " WHERE `user_id` = ? and `author_id` = ?";
        $res = Service_Db::i()->dbh()->prepare($sql);
        $res = $res->execute([$followerId, $authorId]);
        Base_Memcache::i()->delete($mcKey);
        return $res;
    }

}
<?php
class Feed_Service_Article
{
    const MC_FLOOD = 'mc_flood:';
    const MC_FLOOD_HOUR = 'mc_flood_h:';

    const HOUR_COUNT_ARTICLES = 100;

    public static function canEditArticle($userId, $postId)
    {
        $post = (new Feed_Dao_Article())->getById($postId);
        if (empty($post)) {
            return false;
        }

        if ($post['author_id'] != $userId) {
            return false;
        }

        return true;
    }
    
    public static function canAddArticle($userId)
    {
        $mcLock = self::MC_FLOOD . $userId;
        $mcLockHour = self::MC_FLOOD_HOUR . $userId;

        if (!Base_Memcache::i()->add($mcLock, 1, 3)) {
            return false;
        };

        $cnt = Base_Memcache::i()->incr($mcLockHour, 1, false, 3600);
        if ($cnt > self::HOUR_COUNT_ARTICLES) {
            return false;
        }

        return true;
    }

    public static function getAllFeed($userId, $limit, $offset)
    {
        $dao = new Feed_Dao_Article();
        $res = $dao->getAllArticles(10, 0);
        if (empty($res)) {
            return [];
        }

        return self::prepare($userId, $res);
    }

    public static function getFeed($userId, $limit, $offset)
    {
        $myFollows = (new Follow_Dao_Base())->getUserById($userId);
        if (empty($myFollows)) {
            return false;
        }

        $dao = new Feed_Dao_Article();

        $res = $dao->getUserFeed($userId, $myFollows, $limit, $offset);
        if (empty($res)) {
            return [];
        }

        return self::prepare($userId, $res);
    }

    public static function getMyFeed($userId, $limit, $offset)
    {
        $dao = new Feed_Dao_Article();
        $res = $dao->getMyArticles($userId, 10, 0);
        if (empty($res)) {
            return [];
        }

        return self::prepare($userId, $res);
    }

    public static function prepareText($text)
    {
        $text = str_replace("\n", '<br>', $text);
        return $text;
    }

    public static function prepare($userId, $res)
    {
        $myFollows = (new Follow_Dao_Base())->getUserById($userId);

        $authors = array_column($res, 'author_id');
        $users = (new User_Dao_Base())->getMultiple($authors);
        
        foreach ($res as &$article) {
            $article['author_name'] = $users[$article['author_id']]['name'];
            $article['can_edit'] = ($userId == $article['author_id']);
            $article['can_follow'] = !in_array($article['author_id'], $myFollows) && ($userId != $article['author_id']);
            $article['is_me'] = ($userId == $article['author_id']);
            $article['insert_date'] = date('Y-m-d H:i', $article['insert_date']);
            $article['origin_text'] = $article['article_text'];
            $article['article_text'] = self::prepareText($article['article_text']);
            if (!empty($article['update_date'])) {
                $article['update_date'] = date('Y-m-d H:i', $article['update_date']);
            }
        }

        return $res;
    }

}
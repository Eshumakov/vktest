<?php
class Feed_Dao_Article
{
    const TABLE = 'articles';
    const TABLE_ARCHIVE = 'articles_archive';

    const MC_MY_ARTICLES = 'mc_articles:';
    const MC_ALL_ARTICLES = 'mc_articles_all:';
    const MC_MY_FEED = 'mc_feed:';

    public function add($author_id, $title, $message)
    {
        $sql = "INSERT INTO " . self::TABLE . " (`author_id`, `insert_date`, `title`, `article_text`, `update_date`)
         VALUES (?, ?, ?, ?, ?)";
        $res = Service_Db::i()->dbh()->prepare($sql);
        if ($res->execute([$author_id, time(), $title, $message, null])) {
            return Service_Db::i()->dbh()->lastInsertId();
        }

        return false;
    }

    public function edit($id, $author_id, $title, $message)
    {
        $sql = "INSERT INTO " . self::TABLE . " (`id`, `author_id`, `title`, `article_text`, `update_date`)
         VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE title = values(title), article_text = values(article_text), update_date = values(update_date);";
        $res = Service_Db::i()->dbh()->prepare($sql);
        $res->execute([$id, $author_id, $title, $message, time()]);

        $mc = self::MC_MY_ARTICLES . $author_id . '_' . 10 . '_' . 0;
        Base_Memcache::i()->delete($mc);
    }

    public function getByAuthors($authors, $limit, $offset)
    {
        $in  = str_repeat('?,', count($authors) - 1) . '?';
        
        $sql = "SELECT * FROM " . self::TABLE . " WHERE author_id in ($in) order by insert_date DESC LIMIT $offset, $limit";
        $res = Service_Db::i()->dbh()->prepare($sql);
        $res->execute($authors);
        return $res->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id)
    {
        $sql = "SELECT * FROM " . self::TABLE . " WHERE id = ?";
        $res = Service_Db::i()->dbh()->prepare($sql);
        $res->execute([$id]);
        return $res->fetch(PDO::FETCH_ASSOC);
    }

    public function getAll($limit, $offset)
    {
        $sql = "SELECT * FROM " . self::TABLE . " order by insert_date DESC LIMIT $offset, $limit";
        $res = Service_Db::i()->dbh()->prepare($sql);
        $res->execute();
        return $res->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllArticles($limit = 10, $offset = 0)
    {
        $mc = self::MC_ALL_ARTICLES . $limit . '_' . $offset;
        $res = Base_Memcache::i()->get($mc);

        if ($res === false) {
            $res = $this->getAll($limit, $offset);
            Base_Memcache::i()->set($mc, $res);
        }

        return $res;
    }

    public function getMyArticles($userId, $limit = 10, $offset = 0)
    {
        $mc = self::MC_MY_ARTICLES . $userId . '_' . $limit . '_' . $offset;
        $res = Base_Memcache::i()->get($mc);
        
        if ($res === false) {
            $res = $this->getByAuthors([$userId], $limit, $offset);
            Base_Memcache::i()->set($mc, $res);
        }

        return $res;
    }

    /**
     * По идее тут, можно сделать кэш отдельный по автору и хранить ~по 100 записей на автора, и инвалидировать кэш при добавлении статьи от автора
     * Это избавило бы от запросов с in да и вообще избавило бы от 90% запросов
     * Но для данной задачи такой подход избыточен и будет ненужным усложнением логики
     * 
     * @param $userId
     * @param $authorsIds
     * @param $limit
     * @param $offset
     * @return array|string
     */
    public function getUserFeed($userId, $authorsIds, $limit, $offset)
    {
        $mc = self::MC_MY_FEED . $userId . '_' . $limit . '_' . $offset;
        $res = Base_Memcache::i()->get($mc);

        if ($res === false) {
            $res = $this->getByAuthors($authorsIds, $limit, $offset);
            Base_Memcache::i()->set($mc, $res);
        }

        return $res;
    }

}
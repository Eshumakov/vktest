<?php
class Follow_Controller_Index extends Base_Controller
{
    public function __construct($view)
    {
        parent::__construct($view);
    }

    public function preProcess()
    {
        parent::preProcess(); // TODO: Change the autogenerated stub
        if (!$this->USER) {
            return $this->needAuthUser();
        }
        return true;
    }

    public function indexAction()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            return $this->getMy();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->follow();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
            return $this->unFollow();
        }
    }

    public function unFollow()
    {
        $authorId = $this->getParam('author_id');
        $res = (new Follow_Dao_Base())->getUserById($this->USER->getId());
        
        if (!in_array($authorId, $res)) {
            return $this->errorInvalidParameters(['message' => 'You already unfollowed']);
        }

        (new Follow_Dao_Base())->delete($this->USER->getId(), $authorId);

        return $this->view->setAjax(['success' => 1]);
    }

    public function follow()
    {
        $authorId = $this->getParam('author_id');
        if ($authorId == $this->USER->getId()) {
            return $this->errorInvalidParameters(['message' => 'You can\'t follow yourself']);
        }
        $res = (new Follow_Dao_Base())->getUserById($this->USER->getId());
        if (in_array($authorId, $res)) {
            return $this->errorInvalidParameters(['message' => 'You already followed']);
        }

        (new Follow_Dao_Base())->add($this->USER->getId(), $authorId);

        return $this->view->setAjax(['success' => 1]);
    }

    public function getMy()
    {
        $limit = $this->getParam('limit', 10);
        $offset = $this->getParam('offset', 0);


        $res = (new Follow_Dao_Base())->getUserById($this->USER->getId());
        if ($limit) {
            $res = array_splice($res, $offset, $limit);
        }

        $users = [];
        if (!empty($res)) {
            $users = (new User_Dao_Base())->getMultiple($res);
        }

        return $this->view->setAjax(['follows' => $users]);
    }
}
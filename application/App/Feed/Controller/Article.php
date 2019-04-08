<?php
class Feed_Controller_Article extends Base_Controller
{
    public function __construct($view)
    {
        parent::__construct($view);
        if (!$this->USER) {
            return $this->needAuthUser();
        }
    }

    public function indexAction()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            return $this->getOne();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            return $this->editOne();
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->addOne();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
            return $this->deleteOne();
        }
    }

    public function getOne()
    {

    }

    public function editOne()
    {
        $title = $this->getParam('title');
        $message = $this->getParam('message');
        $id = $this->getParam('id');

        if (!$title || !$message) {
            return $this->errorInvalidParameters(['message' => 'need title and message for new article']);
        }

        if (!Feed_Service_Article::canEditArticle($this->USER->getId(), $id)) {
            return $this->errorInvalidParameters(['message' => 'You can not edit this article']);
        }

        (new Feed_Dao_Article())->edit($id, $this->USER->getId(), $title, $message);

        return $this->view->setAjax(['success' => 1, 'id' => $id]);
    }

    public function addOne()
    {
        $title = $this->getParam('title');
        $message = $this->getParam('message');
        
        if (!$title || !$message) {
            return $this->errorInvalidParameters(['message' => 'need title and message for new article']);
        }

        if (!Feed_Service_Article::canAddArticle($this->USER->getId())) {
            return $this->errorInvalidParameters(['message' => 'Time limit reached']);
        }

        $newId = (new Feed_Dao_Article())->add($this->USER->getId(), $title, $message);
        if (!$newId) {
            return $this->errorInvalidParameters(['message' => 'Unknown error']);
        }

        return $this->view->setAjax(['success' => 1, 'id' => $newId]);
    }
}
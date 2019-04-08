<?php
class Feed_Controller_GetUser extends Base_Controller
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
            return $this->getMy();
        }
    }

    public function getMy()
    {
        $userId = $this->getParam('userId');
        $res = Feed_Service_Article::getMyFeed($userId, 10, 0);

        return $this->view->setAjax(['articles' => $res]);
    }
}
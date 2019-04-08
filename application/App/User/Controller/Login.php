<?php
class User_Controller_Login extends User_Controller_Abstract
{

    public function indexAction()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            return $this->login();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
            return $this->unlogin();
        }

    }

    public function unlogin()
    {

    }

    public function login()
    {
        $login = $this->getParam('login');
        $password = $this->getParam('password');
        if (empty($login) || empty($password)) {
            return $this->errorInvalidParameters(['msg' => 'login and password can not be empty']);
        }

        $dao = new User_Dao_Base();
        $user = $dao->getByLogin($login);
        if (empty($user)) {
            return $this->errorInvalidParameters(['msg' => 'login or password incorrect']);
        }

        $password = md5($password);
        if ($password != $user['password']) {
            return $this->errorInvalidParameters(['msg' => 'login or password incorrect']);
        }

        $user['hash'] = User_Service_Base::makeLoginHash($user['id'], $password, Base_Service::getIP());
        
        return $this->view->setAjax($user);
    }
}
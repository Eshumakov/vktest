<?php

class Base_AdminController extends Base_Controller{
    /* @var Base_View $view */
    protected $view;
    protected $USER;
    public function __construct($view)
    {
        $this->view = $view;
        $this->view->setLayout([
            'view/admin/top.phtml',
            'view/admin/bottom.phtml'
        ]);
    }

    public function getParam($name, $def = null, $type = null)
    {
        $val = false;
        if (isset($_GET[$name])) {
            $val = $_GET[$name];
        } elseif (isset($_POST[$name])) {
            $val  = $_POST[$name];
        }

        if (!$val) {
            $val = $def;
        }

        if ($type == 'HTML') {
            return $val;
        }


        return htmlspecialchars($val);
    }

    public function preProcess()
    {
        $this->USER = $this->getUser();
        if (!$this->USER && strpos($this->getUrl(), '/admin/index/login/') !== 0) {
            return $this->redirect('/admin/index/login/');
        }
    }

    public function getUser()
    {
        return (new Base_User())->getUser();
    }

    public function cookie($name, $val = false, $time = 0)
    {
        if (!$val) {
            return $_COOKIE[$name];
        } else {
            return setcookie($name, $val, $time);
        }


    }

    function redirect($redirect_url, $status = null)
    {
        if ($status == 301) {
            header('HTTP/1.1 301 Moved Permanently');
        }else if ($status == 401) {
            header('HTTP/1.1 401  Unauthorized');
        } else {
            header('HTTP/1.1 200 OK');
        }

        header('Location: http://'.$_SERVER['HTTP_HOST'].$redirect_url);
        exit();
    }

}
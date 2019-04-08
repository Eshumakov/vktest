<?php

class Base_Controller {

    const ERROR_NEED_AUTH = 'need_auth';
    const ERROR_INVALID_PARAMS = 'invalid_params';

    /* @var Base_View $view */
    protected $view;
    /* @var Base_Model_User $view */
    protected $USER;
    public function __construct($view)
    {
        $this->view = $view;
        $this->view->newTemplate = $this->getParam('new_view');
        $this->USER = User_Service_Base::getUser();
        $this->preProcess();
    }

    public static function getPageUrl()
    {
        return $_SERVER['REQUEST_URI'];
    }

    public function getUrl()
    {
        return $_SERVER['REQUEST_URI'];
    }

    public function getParam($name, $def = null, $type = null)
    {
        $val = false;

        if (strpos($name, '*')) {
            $fName = str_replace('*', '', $name);

            foreach ($_GET as $n => $v) {
                if (strpos($n, $fName) !== false) {
                    $val[$n] = $v;
                }
            }

            foreach ($_POST as $n => $v) {
                if (strpos($n, $fName) !== false) {
                    $val[$n] = $v;
                }
            }

            if ($type == 'ARR' && is_array($val)) {
                return $val;
            }

            if (is_array($val)) {
                return reset($val);
            }
        }

        if (isset($_GET[$name])) {
            $val = $_GET[$name];
        } elseif (isset($_REQUEST[$name])) {
            $val = $_REQUEST[$name];
        } elseif (isset($_POST[$name])) {
            $val  = $_POST[$name];
        }

        if ($_SERVER['REQUEST_METHOD'] === 'PUT' || $_SERVER['REQUEST_METHOD'] === 'DELETE') {
            parse_str(file_get_contents("php://input"),$post_vars);
            if (isset($post_vars[$name])) {
                $val = $post_vars[$name];
            }
        }

        if ($val === false) {
            $val = $def;
        }

        if ($type == 'ARR' && is_array($val)) {
            return $val;
        }
        if ($type == 'HTML') {
            return $val;
        }


        return htmlspecialchars($val);
    }

    public function preProcess()
    {
        return true;
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

    public function needAuthUser()
    {
        $this->view->setAjax(['error' => self::ERROR_NEED_AUTH]);
        return false;
    }

    public function errorInvalidParameters($params)
    {
        return $this->view->setAjax(['error' => self::ERROR_INVALID_PARAMS, 'params' => $params]);
    }
}
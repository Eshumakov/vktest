<?php
class Base_View {

    private $data = array();
    public $tpl = '';
    public $css = array();
    public $js = array();
    public $noLayout = false;
    public $_ajax = false;

    public $layout = [
        'view/layout/top.phtml',
        'view/layout/bottom.phtml'
    ];


    public function __get($name)
    {
        return isset($this->data[$name]) ? $this->data[$name] : null;
    }

    public function __set($name, $val)
    {
        $this->data[$name] = $val;
    }

    public function norender()
    {
        $this->noLayout = true;
        return $this;
    }

    public function setTemplatesPath($path)
    {
        $this->__set('tplPath', $path);
    }

    public function getTemplatePath()
    {
        return $this->__get('tplPath');
    }

    public function renderAjax($array)
    {
        header("Content-Type: application/json;charset=utf-8");
        header('Content-type: text/html');

        ob_start();
        echo  json_encode($array);
        return ob_get_clean();
    }

    public function renderStr($str)
    {
        ob_start();
        echo  $str;
        return ob_get_clean();
    }

    public function render($str = null)
    {
        if (!empty($_SERVER['HTTP_ORIGIN'])) {
            header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
        }
        header('Access-Control-Allow-Headers: *');
        header('Access-Control-Allow-Headers: *');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, HEAD');

        if ($this->_ajax) {
            return $this->renderAjax($this->_ajax);
        }

        if ($str) {
            return $this->renderStr($str);
        }

        $path = $this->getTemplatePath();
        ob_start();
        if ($this->noLayout) {
            if ($this->tpl) {
                include $path . $this->tpl;
            }
        } else {
            $this->renderHeader();
            include $path . $this->tpl;
            $this->renderBottom();
        }
        return ob_get_clean();
    }

    public function addJs($jsFile)
    {
        $this->js[$jsFile] = 1;
        return $this;
    }

    public function addCss($cssFile)
    {
        $this->css[$cssFile] = 1;
        return $this;
    }

    public function renderBottom()
    {
        include $this->layout[1];
    }

    public function renderHeader()
    {
        $this->cssString = '';
        if (!empty($this->css)) {
            foreach (array_keys($this->css) as $cssFile) {
                $this->cssString .= "<link rel='stylesheet' type='text/css' href='/css/" . $cssFile . ".css?v=" . time() . "' />\n";//debugMode
            }
        }

        if (!empty($this->js)) {
            foreach (array_keys($this->js) as $cssFile) {
                $this->jsString .= '<script src="/js/' . $cssFile . '.js?v=' . time() . '"></script>' . "\n";//debugMode
            }
        }

        include $this->layout[0];
    }

    /**
     * @param array $layout
     */
    public function setLayout($layout)
    {
        $this->layout = $layout;
    }

    /**
     * @param boolean $ajax
     */
    public function setAjax($ajax)
    {
        $this->_ajax = $ajax;
        return $this;
    }
}
<?php

class Base_App {

    public $request;

    public function __construct()
    {
        define('TIME', time());
        $dbConfig = Base_Config::getDbConfig();
        if ($dbConfig) {
            Service_Db::i($dbConfig);
        }
    }

    public function checkRoutes($url)
    {
        $conf = Base_Config::getRoutes();

        foreach ($conf as $pattern => $module) {
            $pattern = str_replace('/', '\/', $pattern);
            preg_match("/$pattern/", $url, $rout);

            if (!$rout) {
                continue;
            }
            $paramName = false;
            if (!empty($module[3])) {
                list($directory, $controllerName, $actionName, $paramName) = $module;
            } else {
                list($directory, $controllerName, $actionName) = $module;
            }

            if ($paramName) {
                $_POST[$paramName] = $rout[1];
            }

            foreach ($rout as $key => $item) {
                if ($key == 0) {
                    continue;
                }


                $directory = str_replace('$' . $key, $item, $directory);
                $controllerName = str_replace('$' . $key, $item, $controllerName);
                $actionName = str_replace('$' . $key, $item, $actionName);
            }

            $controllerName = $directory . '_Controller_' . $controllerName;
        }
        
        if (empty($directory)) {
            return false;
        }

        return [$directory, $controllerName, $actionName];
    }

    public function checkByUrl($url)
    {

        if (strpos($url, '/') === 0) {
            $url = substr($url,1);
        }

        $url = preg_replace('/(.*)\?(.*)/', "$1", $url);
        $urlParts = explode('/', $url);

        $actionIndex = 2;
        $controllerIndex = 1;
        $directoryIndex = 0;



        $directory  = !empty($urlParts[$directoryIndex]) ? ucfirst($urlParts[$directoryIndex]) : 'Index';
        $controller  = !empty($urlParts[$controllerIndex]) ? ucfirst($urlParts[$controllerIndex]) : 'Index';

        $controllerName = $directory . '_Controller_' . $controller;


        if (isset($urlParts[$actionIndex]) && strpos($urlParts[$actionIndex], '.')) $urlParts[$actionIndex] = substr($urlParts[$actionIndex],0,strpos($urlParts[$actionIndex], '.'));
        $actionName = !empty($urlParts[$actionIndex]) ? strtolower($urlParts[$actionIndex]) . 'Action' : 'indexAction';
        return [$directory, $controllerName, $actionName];
    }

    public function run()
    {
        session_start();
        $url = $_SERVER['REQUEST_URI'];
        $url = strstr($url, '?', true) ? strstr($url, '?', true) : $url;
        $view = new Base_View();

        list($directory, $controllerName, $actionName) = $this->checkByUrl($url);
        /* @var Base_Controller  $controller */
        if (!class_exists($controllerName) || !method_exists($controllerName, $actionName)) {
            if ($rout = $this->checkRoutes($url)) {
                list($directory, $controllerName, $actionName) = $rout;
            }
        }

        if (!class_exists($controllerName) || !method_exists($controllerName, $actionName)) {
            header("HTTP/1.x 404 Not Found");
            throw new Exception('page not found', 404);
            return;
        }
        
        if (is_dir(__DIR__. '/../App/' . $directory . '/Templates/')) {
            $view->setTemplatesPath($directory . '/Templates/');
        };
        $controller = new $controllerName($view);
        if ($controller->preProcess()) {
            $controller->$actionName();
        };

        echo  $view->render();
        session_write_close();
    }
}
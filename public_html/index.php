<?php
chdir('../');
set_time_limit(5);
$GLOBALS['time_start'] = microtime(true);
$debug = 1;



if ($debug) {
    ini_set('error_reporting', E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
}


ini_set('include_path',
    ini_get('include_path') . PATH_SEPARATOR .
    'Lib' . PATH_SEPARATOR .
    'application/'. PATH_SEPARATOR .
    'vendor/'. PATH_SEPARATOR .
    'application/App'. PATH_SEPARATOR .
    '../'
);
define ( 'DOCUMENT_ROOT', dirname ( __FILE__ ) );

spl_autoload_register('_autoload', true, false);
function _autoload($class)
{
    $class = ltrim($class, '\\');
    $file = '';
    if ($ns = strrpos($class, '\\')) {

        $namespace = substr($class, 0, $ns);
        $class = substr($class, $ns+1);
        $file = strtr($namespace, '\\', DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }
    $file .= strtr($class, '_', DIRECTORY_SEPARATOR) . '.php';

    try {
        @include $file;
    } catch (Exception $e) {
        return false;
    }
}

try {
$app = new Base_App();
$app->run();
} catch (Exception $e) {
    echo  $e->getMessage(), "\n";
}
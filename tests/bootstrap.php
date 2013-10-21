<?php

set_include_path(dirname(__FILE__) . '/../' . PATH_SEPARATOR . get_include_path());

require_once 'vendor/autoload.php';

function customAutoLoader( $class )
{
    $file = rtrim(dirname(__FILE__), '/') . '/' . $class . '.php';
    if ( file_exists($file) ) {
        require $file;
    } else {
        return;
    }
}

spl_autoload_register('customAutoLoader');

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'config.php';

$driver = new \Aurora\Drivers\MySQLDriver($config['host'], $config['db'], $config['port'], $config['user'], $config['password']);
\Aurora\Dbal::init($driver);
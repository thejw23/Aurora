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

$db = getenv('DB');

if (file_exists(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'config.php'))
    require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'config.php';
else
    require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'mysql_config.php';

if ($db == 'mysql') {
    $driver = new \Aurora\Drivers\MySQLDriver($config['host'], $config['db'], $config['port'], $config['user'], $config['password']);
    \Aurora\Dbal::init($driver);
} else {
    $driver = new \Aurora\Drivers\SQLiteDriver('', '');
    \Aurora\Dbal::init($driver);
}
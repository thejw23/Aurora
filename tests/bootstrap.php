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

if ($db != 'sqlite')
    require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . $db . '_.php';

if ($db == 'sqlite') {
    $driver = new \Aurora\Drivers\SQLiteDriver('', '');
    \Aurora\Dbal::init($driver);
} else {
    switch ($db) {
        case 'mysql':
            $driverName = 'MySQLDriver';
        break;
        
        case 'postgresql':
            $driverName = 'PostgreSQLDriver';
        break;
    }
    
    $driver = new \Aurora\Drivers\$driverName($config['host'], $config['db'], $config['port'], $config['user'], $config['password']);
    \Aurora\Dbal::init($driver);
}
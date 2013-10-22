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

if ($db != 'sqlite3')
    require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . $db . '_config.php';

if ($db == 'sqlite') {
    $driver = new \Aurora\Drivers\SQLiteDriver('', '');
    \Aurora\Dbal::init($driver);
} else {
    switch ($db) {
        case 'mysql':
            $driver = new \Aurora\Drivers\MySQLDriver(
                $config['host'],
                $config['db'],
                $config['port'],
                $config['user'],
                $config['password']
            );
        break;
        
        case 'postgresql':
            $driver = new \Aurora\Drivers\PostgreSQLDriver(
                $config['host'],
                $config['db'],
                $config['port'],
                $config['user'],
                $config['password']
            );
        break;
    }
    \Aurora\Dbal::init($driver);
}
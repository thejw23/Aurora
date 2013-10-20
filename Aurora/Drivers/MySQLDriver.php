<?php

namespace Aurora\Drivers;

class MySQLDriver extends \Aurora\Drivers\BaseDriver
{
    private $host;
    private $dbname;
    private $port;
    private $user;
    private $password;
    
    public function __construct($host, $dbname, $port, $user, $password)
    {
        $this->host = $host;
        $this->dbname = $dbname;
        $this->port = (int) $port;
        $this->user = $user;
        $this->password = $password;
    }
    
    public function getConnection()
    {
        return new \PDO(
            "mysql:host={$this->host};dbname={$this->dbname};" . 
            "port={$this->port}",
            $this->user,
            $this->password
        );
    }
}
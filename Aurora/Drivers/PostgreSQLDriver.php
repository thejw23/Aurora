<?php

namespace Aurora\Drivers;

class PostgreSQLDriver extends \Aurora\Drivers\BaseDriver
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
            "pgsql:host={$this->host};port={$this->port};" . 
            "dbname={$this->dbname};user={$this->user};" .
            "password={$this->password}"
        );
    }
}
<?php

namespace Aurora\Drivers;

class SQLiteDriver extends \Aurora\Drivers\BaseDriver
{
    private $file;
    private $user;
    private $password;
    
    public function __construct($user, $password, $file = ':memory:')
    {
        $this->file = $file;
        $this->user = $user;
        $this->password = $password;
    }
    
    public function getConnection()
    {
        return new PDO(
            'sqlite:' . $this->file,
            $this->user,
            $this->password
        );
    }
}
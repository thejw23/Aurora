<?php

use \Aurora\Table;
use \Aurora\Column;
use \Aurora\Types\Int;
use \Aurora\Types\String;
use \Aurora\Query;

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'config.php';

class User extends Table
{
    protected $user_id;
    protected $user_name;
    protected $user_mail;
    protected $user_password;
    
    protected function setup()
    {
        $this->name = 'users';
        $this->user_id = new Column(new Int());
        $this->user_id->primaryKey = true;
        $this->user_id->autoIncrement = true;
        $this->user_name = new Column(new String(80));
        $this->user_name->unique = true;
        $this->user_name->default = '';
        $this->user_mail = new Column(new String(80));
        $this->user_password = new Column(new String(80));
    }
}

$driver = new \Aurora\Drivers\MySQLDriver($config['host'], $config['db'], $config['port'], $config['user'], $config['password']);
\Aurora\Dbal::init($driver);

class CreateTableTest extends PHPUnit_Framework_TestCase
{
    public function testCreateSimpleTable()
    {
        $user = User::instance();    
        $sql = "CREATE TABLE users (user_id INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,user_name VARCHAR(80) NOT NULL UNIQUE DEFAULT '',user_mail VARCHAR(80) NOT NULL,user_password VARCHAR(80) NOT NULL)";
        $this->assertEquals($sql, (string) $user);
        $this->assertEquals(true, $user->createTable());
    }
    
    public function testInsertRow()
    {
        $user = User::instance();
        $user->user_name = "Bob Doe";
        $user->user_mail = 'bobdoe@bobmail.com';
        $user->user_password = 'supersecret';
        $this->assertEquals(true, $user->save());
    }
    
    public function testGetAllRows()
    {
        $users = User::query()->all();
        $this->assertEquals("Bob Doe", $users[0]->user_name);
    }
    
    public function testGetFirstRow()
    {
        $user = User::query()->first();
        $this->assertEquals("Bob Doe", $user->user_name);
    }
    
    public function testGetRow()
    {
        $user = User::query()->get(0);
        $this->assertEquals("Bob Doe", $user->user_name);
        $user = User::query()->get(1);
        $this->assertEquals(false, $user);
    }
    
    public function testLimitRow()
    {
        $user = User::query()->limit(0, 1);
        $this->assertEquals("Bob Doe", $user->user_name);
        $users = User::query()->limit(2, 6);
        $this->assertEquals(0, count($users));
    }
    
    public function testUpdateRow()
    {
        $user = User::instance();
        $user->user_id = 1;
        $user->user_name = "Bob Dylan";
        $user->user_mail = 'bobdyaln@bobmail.com';
        $user->user_password = 'supersupersecret';
        $this->assertEquals(true, $user->save(true));
    }
    
    public function testDeleteRow()
    {
        $user = User::instance();
        $user->user_id = 1;
        $user->user_name = "Bob Dylan";
        $user->user_mail = 'bobdyaln@bobmail.com';
        $user->user_password = 'supersupersecret';
        $this->assertEquals(true, $user->remove(true));
    }
    
    public function testDropTable()
    {
        $user = User::instance();
        $this->assertEquals(true, $user->dropTable());
    }
}
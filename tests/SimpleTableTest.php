<?php

use \Aurora\Table;
use \Aurora\Column;
use \Aurora\Types\Int;
use \Aurora\Types\String;
use \Aurora\Query;

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

class SimpleTableTest extends PHPUnit_Framework_TestCase
{
    public function testCreateSimpleTable()
    {
        $user = new User();    
        $sql = "CREATE TABLE users (user_id INTEGER NOT NULL AUTO_INCREMENT,user_name VARCHAR(80) NOT NULL UNIQUE DEFAULT '',user_mail VARCHAR(80) NOT NULL,user_password VARCHAR(80) NOT NULL,PRIMARY KEY (user_id))";
        $this->assertEquals($sql, (string) $user);
        $this->assertEquals(true, $user->createTable());
    }
    
    public function testInsertRow()
    {
        $user = new User();
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
        $user = new User();
        $user->user_id = 1;
        $user->user_name = "Bob Dylan";
        $user->user_mail = 'bobdyaln@bobmail.com';
        $user->user_password = 'supersupersecret';
        $this->assertEquals(true, $user->save(true));
    }
    
    public function testOrderBy()
    {
        $user = new User();
        $user->user_name = "Michael";
        $user->user_mail = 'michael@bobmail.com';
        $user->user_password = 'supersupersecret';
        $user->save();
        
        $users = User::query()->orderBy('user_id', 'DESC')->all();
        $this->assertEquals("Michael", $users[0]->user_name);
    }
    
    public function testWhere()
    {
        $users = User::query()->where("user_name = ?", array("Michael"))->all();
        $this->assertEquals("Michael", $users[0]->user_name);
    }
    
    public function testFilterBy()
    {
        $users = User::query()
            ->filterBy(array('user_name', 'Michael'))
            ->orderBy('user_id', 'DESC')
            ->all();
        $this->assertEquals("Michael", $users[0]->user_name);
        
        $users = User::query()
            ->filterBy(array(
                array('user_name', 'Michael'),
                'OR',
                array('user_name', 'Bob Dylan')
            ))
            ->all();
        $this->assertEquals(2, count($users));
        
        $users = User::query()
            ->filterBy(array(
                'user_name',
                'IN',
                array('Michael', 'Bob Dylan')
            ))
            ->all();
        $this->assertEquals(2, count($users));
        
        $users = User::query()
            ->filterBy(array(
                'user_name',
                'LIKE',
                'M%'
            ))
            ->all();
        $this->assertEquals(1, count($users));
    }
    
    public function testDeleteRow()
    {
        $user = new User();
        $user->user_id = 1;
        $user->user_name = "Bob Dylan";
        $user->user_mail = 'bobdyaln@bobmail.com';
        $user->user_password = 'supersupersecret';
        $this->assertEquals(true, $user->remove(true));
    }
    
    public function testDropTable()
    {
        $user = new User();
        $this->assertEquals(true, $user->dropTable());
    }
}
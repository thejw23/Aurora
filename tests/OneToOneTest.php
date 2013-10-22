<?php

use \Aurora\Table;
use \Aurora\Column;
use \Aurora\Types\Int;
use \Aurora\Types\String;
use \Aurora\Query;
use \Aurora\ForeignKey;
use \Aurora\Relationship;

class OTO_User extends Table
{
    protected $user_id;
    protected $user_name;
    protected $user_mail;
    protected $user_password;
    protected $profile;
    
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
        
        $this->profile = new Relationship('Profile', 'user_id', 'user_id');
    }
}

class Profile extends Table
{
    protected $profile_id;
    protected $user_id;
    protected $bio;
    protected $user;
    
    protected function setup()
    {
        $this->name = 'profiles';
        
        $this->profile_id = new Column(new Int());
        $this->profile_id->primaryKey = true;
        $this->profile_id->autoIncrement = true;
        $this->user_id = new Column(new Int());
        $this->user_id->unique = true;
        $this->user_id->foreignKey = new ForeignKey(
            'OTO_User',
            'user_id',
            'user_id',
            'CASCADE',
            'CASCADE'
        );
        $this->bio = new Column(new String(255));
        $this->bio->default = '';
        
        $this->user = new Relationship('OTO_User', 'user_id', 'user_id');
    }
}

class OneToOneTest extends PHPUnit_Framework_TestCase
{
    public function testCreateTables()
    {
        $user = new OTO_User();    
        $this->assertEquals(true, $user->createTable());
        
        $profile = new Profile();
        $this->assertEquals(true, $profile->createTable());
    }
    
    public function testInsertRow()
    {
        $user = new OTO_User();
        $user->user_name = "Bob Doe";
        $user->user_mail = 'bobdoe@bobmail.com';
        $user->user_password = 'supersecret';
        $this->assertEquals(true, $user->save());
        
        $user->user_name = "Bob Doel";
        $this->assertEquals(true, $user->save());
        $users = OTO_User::query()
            ->filterBy(array('user_name', 'LIKE', 'Bob D%'))
            ->all();
        $this->assertEquals(1, count($users));
        
        $profile = new Profile();
        $profile->user_id = $user->user_id;
        $profile->bio = "Fancy ninja.";
        $this->assertEquals(true, $profile->save());
    }
    
    public function testRelation()
    {
        $user = OTO_User::query()
            ->filterBy(array('user_name', 'Bob Doel'))
            ->first();
        $this->assertEquals('Fancy ninja.', $user->profile->bio);
    }
    
    public function testDropTable()
    {
        $user = new OTO_User();
        $profile = new Profile();
        
        $exceptionThrown = false;
        
        try {
            $this->assertEquals(true, $user->dropTable());
            $this->assertEquals(true, $profile->dropTable());
        } catch (\Aurora\Error\DatabaseException $e) {
            $exceptionThrown = true;
            $this->assertEquals(true, true);
        }
        
        if ($exceptionThrown) {
            $this->assertEquals(true, $profile->dropTable());
            $this->assertEquals(true, $user->dropTable());
        }
    }
}
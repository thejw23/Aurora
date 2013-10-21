<?php

use \Aurora\Table;
use \Aurora\Column;
use \Aurora\Types\Int;
use \Aurora\Types\String;
use \Aurora\Query;
use \Aurora\ForeignKey;
use \Aurora\Relationship;

class OTM_User extends Table
{
    protected $user_id;
    protected $user_name;
    protected $user_mail;
    protected $user_password;
    protected $posts;
    
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
        
        $this->posts = new Relationship('Post', 'user_id', 'user_id', false);
    }
}

class Post extends Table
{
    protected $post_id;
    protected $user_id;
    protected $title;
    protected $user;
    
    protected function setup()
    {
        $this->name = 'posts';
        
        $this->post_id = new Column(new Int());
        $this->post_id->primaryKey = true;
        $this->post_id->autoIncrement = true;
        $this->user_id = new Column(new Int());
        $this->user_id->foreignKey = new ForeignKey(
            'OTO_User',
            'user_id',
            'user_id',
            'CASCADE',
            'CASCADE'
        );
        $this->title = new Column(new String(255));
        $this->title->default = '';
        
        $this->user = new Relationship('OTO_User', 'user_id', 'user_id');
    }
}

class OneToManyTest extends PHPUnit_Framework_TestCase
{
    public function testCreateTables()
    {
        $user = OTM_User::instance();    
        $sql = "CREATE TABLE users (user_id INTEGER NOT NULL AUTO_INCREMENT,user_name VARCHAR(80) NOT NULL UNIQUE DEFAULT '',user_mail VARCHAR(80) NOT NULL,user_password VARCHAR(80) NOT NULL,PRIMARY KEY (user_id))";
        $this->assertEquals($sql, (string) $user);
        $this->assertEquals(true, $user->createTable());
        
        $post = Post::instance();
        $sql = "CREATE TABLE posts (post_id INTEGER NOT NULL AUTO_INCREMENT,user_id INTEGER NOT NULL,title VARCHAR(255) NOT NULL DEFAULT '',PRIMARY KEY (post_id),FOREIGN KEY (user_id) REFERENCES users(user_id) ON UPDATE CASCADE ON DELETE CASCADE)";
        $this->assertEquals($sql, (string) $post);
        $this->assertEquals(true, $post->createTable());
    }
    
    public function testInsertRow()
    {
        $user = OTM_User::instance();
        $user->user_name = "Bob Doe";
        $user->user_mail = 'bobdoe@bobmail.com';
        $user->user_password = 'supersecret';
        $this->assertEquals(true, $user->save());
        
        $post1 = Post::instance();
        $post2 = Post::instance();
        $post3 = Post::instance();
        
        $post1->user_id = $user->user_id;
        $post1->title = 'Post 1';
        $this->assertEquals(true, $post1->save());
        
        $post2->user_id = $user->user_id;
        $post2->title = 'Post 2';
        $this->assertEquals(true, $post2->save());
        
        $post3->user_id = $user->user_id;
        $post3->title = 'Post 3';
        $this->assertEquals(true, $post3->save());
    }
    
    public function testRelation()
    {
        $user = OTM_User::query()
            ->filterBy(array('user_name', 'Bob Doe'))
            ->first();
        $this->assertEquals(3, count($user->posts));
        $this->assertEquals('Post 3', $user->posts[2]->title);
    }
    
    public function testDropTable()
    {
        $user = OTM_User::instance();
        $post = Post::instance();
        
        try {
            $this->assertEquals(true, $user->dropTable());
            $this->assertEquals(true, $post->dropTable());
        } catch (\Aurora\Error\DatabaseException $e) {
            $this->assertEquals(true, true);
        }
        
        $this->assertEquals(true, $post->dropTable());
        $this->assertEquals(true, $user->dropTable());
    }
}
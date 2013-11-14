<?php
/**
 * Aurora - Fast and easy to use php ORM.
 *
 * @author      José Miguel Molina <hi@mvader.me>
 * @copyright   2013 José Miguel Molina
 * @link        https://github.com/mvader/Aurora
 * @license     https://raw.github.com/mvader/Aurora/master/LICENSE
 * @version     1.0.1
 * @package     Aurora
 *
 * MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

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
        $this->assertEquals(true, $user->remove());
    }
    
    public function testDropTable()
    {
        $user = new User();
        $this->assertEquals(true, $user->dropTable());
    }
}

Aurora 
======

Aurora is a fast and easy to use php ORM built on top of PDO. It currently supports MySQL (MariaDB not tested), SQLite3 and PostgreSQL. 

PSR-0, PSR-1 and PSR-2 compliant.

# Changes in this fork

* added SET NAMES
* added \Aurora\DataQuery for fetching data without creating models
* added load() for getting single row identified by ID
* added \Aurora\PDO\E_PDOStatement for getting full query
* added != and = to \Aurora\SQL\Util $allowedOperators
* added support for profiler
* added to \Aurora\Table method as_array(), getting fields from ie. validation
* TESTS ARE NOT FIXED (yet)

Creating a new model
---------------

```php
<?php

use \Aurora\Table;
use \Aurora\Column;
use \Aurora\Types\Int;
use \Aurora\Types\String;

class User extends Table
{
    // Fields MUST be protected
    protected $user_id;
    protected $user_name;
    protected $user_mail;
    protected $user_password;
    
    // Setup is an abstract function (so you MUST implement it)
    // where you initialize the model fields.
    protected function setup()
    {
        // Set the table name
        $this->name = 'users';
        
        // Create a new column
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

// To create the table for this model you just need to create an instance of User and call
// the createTable method.
$userTable = new User();

$userTable->createTable();
```

Inserting, updating and deleting records
--------------
```php
// Create an instance of the model
$user = new User();

// Fill its fields (not the autoincrement primary key, it will be filled for itself after you
// insert the record)
$user->user_name = "Bob Doe";
$user->user_mail = 'bobdoe@bobmail.com';
$user->user_password = 'supersecret';

// Save the changes
$user->save();

// If you want to update it just change the fields and save it again
$user->user_name = "John Doe";
$user->save();

// Don't want that record anymore? easy
$user->remove();
```

Retrieving records from the database
------------------------
```php
// All the users whose user_name is Michael ordered by user_id in descending order
$users = User::query()
    ->filterBy(array('user_name', 'Michael'))
    ->orderBy('user_id', 'DESC')
    ->all();

// The first 5 users ordered by user_id in descending order starting at the third record
$users = User::query()
    ->orderBy('user_id', 'DESC')
    ->limit(2, 5);

// First user whose name equals Michael or Bob Dylan
$users = User::query()
    ->filterBy(array(
        'user_name',
        'IN',
        array('Michael', 'Bob Dylan')
    ))
    ->first();

// First user whose name starts with a letter 'M'
$users = User::query()
    ->filterBy(array(
        'user_name',
        'LIKE',
        'M%'
    ))
    ->limit(1);
```

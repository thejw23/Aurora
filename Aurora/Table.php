<?php

namespace Aurora;

abstract class Table
{
    public $engine = null;
    public $autoIncrement = null;
    public $characterSet = null;
    public $collation = null;
    public $name = null;
    private $notInserted = true;
    private static $baseProperties = array(
        'engine',
        'autoIncrement',
        'characterSet',
        'collation',
        'name',
        'notInserted',
    );
    
    final protected function __construct()
    {
        // Don't use constructors
    }
    
    final public static function instance()
    {
        $class = get_called_class();
        $classInstance = new $class();
        $classInstance->setup();
        return $classInstance;
    }
    
    private function getProperties()
    {
        return array_keys(get_object_vars($this));
    }
    
    public function __isset($property)
    {
        return in_array($property, $this->getProperties()) &&
            !in_array($property, self::$baseProperties);
    }
    
    public function __get($property)
    {
        if (in_array($property, $this->getProperties()) &&
            !in_array($property, self::$baseProperties)) {
            if ($this->$property instanceof \Aurora\Relationship && is_null($this->$property->value)) {
                $fk = $this->$property->getForeignKey();
                $this->$property->retrieve($this->$fk->value);
            }
            return $this->$property->value;
        } else
            return null;
    }
    
    public function __set($property, $value)
    {
        if (in_array($property, $this->getProperties()) &&
            !in_array($property, self::$baseProperties)) {
            if ($this->$property instanceof \Aurora\Column)
                $this->$property->value = $value;
        }
    }
    
    final public function parseValue($property, $value)
    {
        if ($this->__isset($property)) {
            return $this->$property->type->parseValue($value);
        } else 
            throw new \RuntimeException("{$property} property does not exist.");
    }
    
    final public function getName()
    {
        return (!is_null($this->name)) ? $this->name : 'UNNAMED';
    }
    
    final public function getColumns(array &$constraints = array(), array &$primaryKeys = array())
    {
        $columnNames = array_diff(
            $this->getProperties(),
            self::$baseProperties
        );
        
        $columns = array();
        
        foreach ($columnNames as $col) {
            if ($this->$col instanceof \Aurora\Column) {
                $this->$col->name = $col;
                if ($this->$col->foreignKey instanceof \Aurora\ForeignKey)
                    $constraints[] = $this->$col->foreignKey;
                if ($this->$col->primaryKey)
                    $primaryKeys[] = $col;
                $columns[] = $this->$col;
            } elseif ($this->$col instanceof \Aurora\Relationship) {
                continue;
            } else
                throw new \Aurora\Error\CreateTableException("{$col} is not a  \Aurora\Column object.");
        }
        
        return array_merge($columns);
    }
    
    final public function hasColumn($column)
    {
        return count(array_filter($this->getColumns(), 
            function($col) use ($column) {
                return $col->name === $column;
            }
        )) > 0;
    }
    
    abstract protected function setup();
    
    final public function save($forceUpdate = false)
    {
        if ($this->notInserted && !$forceUpdate) {
            $sql = 'INSERT INTO ' . $this->name;
            
            $pk = null;
            
            $columnsToInsert = array_filter(
                $this->getColumns(),
                function($col) use (&$pk) {
                    if (is_null($col->value) && 
                        $col->primaryKey && 
                        $col->autoIncrement) {
                        $pk = $col;
                    }
                    return !is_null($col->value) && 
                        !($col->primaryKey && $col->autoIncrement);
                }
            );
            
            $args = array();
            $keys = join(', ', array_map(
                function($col) use (&$args) {
                    $args[] = $col->value;
                    return $col->name;
                },
                $columnsToInsert
            ));
            
            $values = join(', ', array_map(
                function($col) {
                    return '?';
                },
                $columnsToInsert
            ));
            
            $sql .= " ({$keys}) VALUES ({$values})";

            $id = null;
            $result = \Aurora\Dbal::query($sql, $args, false, $id);
            $this->notInserted = false;
            $pk->value = $pk->type->parseValue($id);
            
            return $result;
        } else {
            $sql = 'UPDATE ' . $this->name . ' SET ';
            $primaryKeys = array();
            $columnsToInsert = array_filter(
                $this->getColumns(),
                function($col) use (&$primaryKeys) {
                    if ($col->primaryKey)
                        $primaryKeys[] = $col;
                    return !is_null($col->value) && !$col->primaryKey;
                }
            );
            
            if (count($primaryKeys) == 0)
                throw new \RuntimeException('Error saving the object. There is not value for the primary key field.');
            
            $args = array();
            $fields = join(', ', array_map(
                function($col) use (&$args) {
                    $args[] = $col->value;
                    return "{$col->name} = ?";
                },
                $columnsToInsert
            ));
            
            $sql .= "{$fields} WHERE ";
            $sql .= \Aurora\SQL\Util::andEqualColumns($primaryKeys);
            
            foreach ($primaryKeys as $key) {
                $args[] = $key->value;
            }

            return \Aurora\Dbal::query($sql, $args, false);
        }
    }
    
    final public function remove()
    {
        $sql = 'DELETE FROM ' . $this->name . ' WHERE ';
        $primaryKeys = array();
        foreach ($this->getColumns() as $col) {
            if ($col->primaryKey) {
                $primaryKeys[] = $col;
            }
        }
        
        if (count($primaryKeys) == 0)
            throw new \RuntimeException('Error deleting the object. There is not value for the primary key field.');
        
        $sql .= \Aurora\SQL\Util::andEqualColumns($primaryKeys);
        $args = array_map(
            function($col) {
                return $col->value;
            },
            $primaryKeys
        );

        return \Aurora\Dbal::query($sql, $args, false);
    }
    
    final public function dropTable()
    {
        $sql = "DROP TABLE {$this->name}";
        return \Aurora\Dbal::query($sql, null, false);
    }
    
    final public function createTable()
    {
        $sql = $this->__toString();
        return \Aurora\Dbal::query($sql, null, false);
    }

    final private function getPrimaryKeyClause($primaryKeys)
    {
        if (count($primaryKeys) < 1)
            throw new \RuntimeException("{$this->name} table does not have a primary key.");

        $fields = join(', ', $primaryKeys);
        return "PRIMARY KEY ({$fields})";
    }
    
    final public function __toString()
    {
        $constraints = array();
        $primaryKeys = array();
        $columns = $this->getColumns($constraints, $primaryKeys);
        $pk = $this->getPrimaryKeyClause($primaryKeys);
        $fields = array_merge($columns, array($pk), $constraints);
        
        $strValue = "CREATE TABLE {$this->name} (";
        $strValue .= join(',', array_map(function($item) {
            return (string) $item;
        }, $fields));
        $strValue .= ')';
        
        return $strValue;
    }
    
    final public function setInserted()
    {
        $this->notInserted = false;
    }
    
    final public static function query()
    {
        $model = get_called_class();
        $instance = $model::instance();
        return new \Aurora\Query($instance->name, $model);
    }
}
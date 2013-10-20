<?php

namespace Aurora;

abstract class Table
{
	public $engine = null;
	public $autoIncrement = null;
	public $characterSet = null;
	public $collation = null;
	public $name = null;
	private $hasPrimaryKey = false;
	private $notInserted = true;
	private static $baseProperties = array(
		'engine',
		'autoIncrement',
		'characterSet',
		'collation',
		'name',
		'hasPrimaryKey',
		'notInserted'
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
			return $this->$property->value;
		} else
			return null;
	}
	
	public function __set($property, $value)
	{
		if (in_array($property, $this->getProperties()) &&
			!in_array($property, self::$baseProperties)) {
			$this->$property->value = $value;
			if ($value instanceof Column && $value->primaryKey) {
				$this->setPrimaryKey();
			}
		}
	}
	
	final public function getName()
	{
		return (!is_null($this->name)) ? $this->name : 'UNNAMED';
	}
	
	final public function setPrimaryKey()
	{
		if ($this->hasPrimaryKey === true)
			throw new \Aurora\Error\CreateTableException('Table ' . $this->getName() . ' already has a primary key.');
		$this->hasPrimaryKey = true;
	}
	
	final public function getColumns()
	{
		$columnNames = array_diff(
			$this->getProperties(),
			self::$baseProperties
		);
		
		$columns = array();
		foreach ($columnNames as $col) {
			$columns[] = $this->$col;
			if ($this->$col instanceof \Aurora\Column)
				$this->$col->name = $col;
			else
				throw new \Aurora\Error\CreateTableException("{$col} is not a  \Aurora\Column object.");
		}
		
		return $columns;
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
			
			$columnsToInsert = array_filter(
				$this->getColumns(),
				function($col) {
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

			return \Aurora\Dbal::query($sql, $args, false);
		} else {
			$sql = 'UPDATE ' . $this->name . ' SET ';
			$primaryKey = null;
			$columnsToInsert = array_filter(
				$this->getColumns(),
				function($col) use (&$primaryKey) {
					if ($col->primaryKey)
						$primaryKey = $col;
					return !is_null($col->value) && !$col->primaryKey;
				}
			);
			
			if (is_null($primaryKey))
				throw new \RuntimeException('Error saving the object. There is not value for the primary key field.');
			
			$args = array();
			$fields = join(', ', array_map(
				function($col) use (&$args) {
					$args[] = $col->value;
					return "{$col->name} = ?";
				},
				$columnsToInsert
			));
			
			$sql .= "{$fields} WHERE {$primaryKey->name} = ?";
			$args[] = $primaryKey->value;

			return \Aurora\Dbal::query($sql, $args, false);
		}
	}
	
	final public function remove()
	{
		$sql = 'DELETE FROM ' . $this->name . ' WHERE ';
		$primaryKey = null;
		foreach ($this->getColumns() as $col) {
			if ($col->primaryKey) {
				$primaryKey = $col;
				break;
			}
		}
		
		if (is_null($primaryKey))
			throw new \RuntimeException('Error deleting the object. There is not value for the primary key field.');
		
		$sql .= "{$primaryKey->name} = ?";
		$args = array($primaryKey->value);

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
	
	final public function __toString()
	{
		$strValue = "CREATE TABLE {$this->name} (";
		$strValue .= join(',', array_map(function($item) {
			return (string) $item;
		}, $this->getColumns()));
		$strValue .= ')';
		
		return $strValue;
	}
}
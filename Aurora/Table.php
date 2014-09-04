<?php
/**
 * Aurora - Fast and easy to use php ORM.
 *
 * @author      José Miguel Molina <hi@mvader.me>
 * @copyright   2013 José Miguel Molina
 * @link        https://github.com/mvader/Aurora
 * @license     https://raw.github.com/mvader/Aurora/master/LICENSE
 * @version     1.0.3
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
namespace Aurora;

/**
 * Table
 *
 * This class is an abstract base class which you can extend to create
 * new models (that is, tables).
 *
 * @package Aurora
 * @author José Miguel Molina
 */
abstract class Table
{
    /**
     * @todo not implemented
     */
    public $engine = null;
    public $autoIncrement = null;
    public $characterSet = null;
    public $collation = null;

    private $changedFields = array();

    /**
     * @var string Table name
     */
    public $tableName = null;

    /**
     * @var bool If the record of that instance has been inserted or not
     */
    private $notInserted = true;

    public $loaded = false;

    /**
     * @var array The base properties
     */
    private static $baseProperties = array(
        'engine',
        'autoIncrement',
        'characterSet',
        'collation',
        'tableName',
        'notInserted',
        'changedFields',
        'loaded',
    );

    /**
     * Constructor
     *
     * Only calls the setup method you should have implemented in your child class
     */
    final public function __construct()
    {
        $this->setup();
    }

    /**
     * Returns the properties of the object
     *
     * @return array
     */
    private function getProperties()
    {
        return array_keys(get_object_vars($this));
    }

    /**
     * Checks for the existance of the property
     *
     * @param  string $property The property
     * @return bool
     */
    public function __isset($property)
    {
        return in_array($property, $this->getProperties()) &&
            !in_array($property, self::$baseProperties);
    }

    /**
     * Returns the value of the requested property.
     *
     * It doesn't return any of the base properties because they don't have a value.
     * This is because the properties you will define in your model will be protected,
     * you can access the base properties as usual because they're public.
     *
     * @param string $property The property
     * @return
     */
    public function __get($property)
    {
        if (in_array($property, $this->getProperties()) &&
            !in_array($property, self::$baseProperties)) {
            if ($this->$property instanceof \Aurora\Relationship && is_null($this->$property->value)) {
                $fk = $this->$property->getForeignKey();
                $this->$property->retrieve($this->$fk->value);
            }

            return $this->$property->value;
        } else {
            return null;
        }
    }

    /**
     * Sets the value for a property
     *
     * @param string $property The property
     * @param mixed  $value    The value
     */
    public function __set($property, $value)
    {
        if (in_array($property, $this->getProperties()) &&
            !in_array($property, self::$baseProperties)) {
            if ($this->$property instanceof \Aurora\Column) {
                $this->$property->value = $value;

                if ($this->loaded) {
                    $this->changedFields[] = $property;
                }
            }
        }
    }

    /**
     * Returns the parsed value of a property
     *
     * @param  string            $property The property
     * @param  mixed             $value    The value
     * @return mixed
     * @throws \RuntimeException If the property does not exist
     */
    final public function parseValue($property, $value)
    {
        if ($this->__isset($property)) {
            if ($this->$property->type instanceof \Aurora\Types\DateTime) {
                return $this->$property->type->retrieveValue($value);
            } else {
                return $this->$property->type->parseValue($value);
            }
        } else {
            throw new \RuntimeException($property . ' property does not exist.');
        }
    }

    /**
     * Returns the name of the table or UNNAMED
     *
     * @return string
     */
    final public function getName()
    {
        return (!is_null($this->tableName)) ? $this->tableName : 'UNNAMED';
    }

    /**
     * Returns the columns of the table and retrieves the constraints and primary keys found.
     *
     * @param  array $constraints The reference of the var where the found constraints will be stored
     * @param  array $primaryKeys The reference of the var where the found primary keys will be stored
     * @return array
     */
    final public function getColumns(array &$constraints = array(), array &$primaryKeys = array())
    {
        // Get the column names
        $columnNames = array_diff(
            $this->getProperties(),
            self::$baseProperties
        );

        $columns = array();

        foreach ($columnNames as $col) {
            if ($this->$col instanceof \Aurora\Column) {
                $this->$col->name = $col;
                if ($this->$col->foreignKey instanceof \Aurora\ForeignKey) {
                    $constraints[] = $this->$col->foreignKey;
                }
                if ($this->$col->primaryKey) {
                    $primaryKeys[] = $col;
                }
                $columns[] = $this->$col;
            } elseif ($this->$col instanceof \Aurora\Relationship) {
                continue;
            }
        }

        return $columns;
    }

    /**
     * Finds if a column exists
     *
     * @param  string $column The column
     * @return bool
     */
    final public function hasColumn($column)
    {
        return count(array_filter($this->getColumns(),
            function ($col) use ($column) {
                return $col->name === $column;
            }
        )) > 0;
    }

    /**
     * Here is where the magic happens. You need to extend that method.
     * Here will be where you setup your columns and relationships
     */
    abstract protected function setup();

    /**
     * Saves the record held by the instance of the model.
     *
     * If the record hasn't been inserted yet it is inserted, otherwhise, it is updated.
     *
     * @param  bool              $forceUpdate Force the update (if you broken something with this, it's up to you)
     * @throws \RuntimeException If there is an error
     */
    final public function save($forceUpdate = false)
    {
        // Hasn't been inserted yet? do it
        if ($this->notInserted && !$forceUpdate) {
            $sql = 'INSERT INTO ' . $this->tableName;

            $pk = null;
            $name = null;

            // The columns to insert removing the autoincremented column keys
            // because, you know, you won't set their values yourself
            $columnsToInsert = array_filter(
                $this->getColumns(),
                function ($col) use (&$pk) {
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
            // Find the column names and the column values
            $keys = join(', ', array_map(
                function ($col) use (&$args) {
                    if ($col->type instanceof \Aurora\Types\DateTime) {
                        $args[] = $col->type->parseValue($col->value);
                    } else {
                        $args[] = $col->value;
                    }

                    return $col->name;
                },
                $columnsToInsert
            ));

            $values = join(', ', array_map(
                function ($col) {
                    return '?';
                },
                $columnsToInsert
            ));

            $sql .= ' (' . $keys . ') VALUES (' . $values . ')';

            // We need the name of the constraint to get the last insert id in postgresql
            if (\Aurora\Dbal::getDriver()
                instanceof \Aurora\Drivers\PostgreSQLDriver
                && $pk != null) {
                $name = $this->tableName . '_' . $pk->name . '_seq';
            }

            $id = null;

            $result = \Aurora\Dbal::query($sql, $args, false, $id, $name);
            $this->notInserted = false;

            // Set the value of primary key to the last insert id
            if ($id !== '0' && !is_null($pk)) {
                $pk->value = $pk->type->parseValue($id);
            }

            return $result;
        } else {
            $sql = 'UPDATE ' . $this->tableName . ' SET ';
            $primaryKeys = array();
            // Get the columns and the primary keys
            $columnsToInsert = array_filter(
                $this->getColumns(),
                function ($col) use (&$primaryKeys) {
                    if ($col->primaryKey) {
                        $primaryKeys[] = $col;
                    }

                    return !is_null($col->value) && !$col->primaryKey and in_array($col->name, $this->changedFields);
                }
            );

            // If there is no primary keys we won't be able to update the record
            if (count($primaryKeys) == 0) {
                throw new \RuntimeException('Error saving the object. There is not value for the primary key field.');
            }

            $args = array();
            $fields = join(', ', array_map(
                function ($col) use (&$args) {
                    if ($col->type instanceof \Aurora\Types\DateTime) {
                        $args[] = $col->type->parseValue($col->value);
                    } else {
                        $args[] = $col->value;
                    }

                    return $col->name . ' = ?';
                },
                $columnsToInsert
            ));

            $sql .= $fields . ' WHERE ';
            $sql .= \Aurora\SQL\Util::andEqualColumns($primaryKeys);

            foreach ($primaryKeys as $key) {
                $args[] = $key->value;
            }

            return \Aurora\Dbal::query($sql, $args, false);
        }
    }

    /**
     * Removes the current record from the database
     *
     * @throws \RuntimeException If there is an error deleting the object.
     */
    final public function remove()
    {
        $sql = 'DELETE FROM ' . $this->tableName . ' WHERE ';
        $primaryKeys = array();
        foreach ($this->getColumns() as $col) {
            if ($col->primaryKey) {
                $primaryKeys[] = $col;
            }
        }

        if (count($primaryKeys) == 0) {
            throw new \RuntimeException('Error deleting the object. There is not value for the primary key field.');
        }

        $sql .= \Aurora\SQL\Util::andEqualColumns($primaryKeys);
        $args = array_map(
            function ($col) {
                return $col->value;
            },
            $primaryKeys
        );

        return \Aurora\Dbal::query($sql, $args, false);
    }

    /**
     * Drops the table
     *
     * @return bool If the table was successfully dropped or not
     */
    final public function dropTable()
    {
        $sql = 'DROP TABLE ' . $this->tableName;

        return \Aurora\Dbal::query($sql, null, false);
    }

    /**
     * Creates the table
     *
     * @return bool If the table was successfully created or not
     */
    final public function createTable()
    {
        $sql = $this->__toString();

        return \Aurora\Dbal::query($sql, null, false);
    }

    /**
     * Returns the primary key clauses for the creation of the table
     *
     * @param  array $primaryKeys The primary keys
     * @return array
     */
    final private function getPrimaryKeyClause($primaryKeys)
    {
        if (count($primaryKeys) < 1) {
            throw new \RuntimeException($this->tableName . ' table does not have a primary key.');
        }

        $fields = join(', ', $primaryKeys);
        if (count($primaryKeys) == 1
            && (\Aurora\Dbal::getDriver() instanceof \Aurora\Drivers\SQLiteDriver
            || \Aurora\Dbal::getDriver() instanceof \Aurora\Drivers\PostgreSQLDriver)) {
            return array();
        } else {
            return array('PRIMARY KEY (' . $fields . ')');
        }
    }

    /**
     * Returns the string representation of the table
     *
     * @return string
     */
    final public function __toString()
    {
        $constraints = array();
        $primaryKeys = array();
        $columns = $this->getColumns($constraints, $primaryKeys);
        $pk = $this->getPrimaryKeyClause($primaryKeys);
        $fields = array_merge($columns, $pk, $constraints);

        $strValue = 'CREATE TABLE ' . $this->tableName . ' (';
        $strValue .= join(',', array_map(
            function ($item) {
                return (string) $item;
            },
            $fields
        ));
        $strValue .= ')';

        return $strValue;
    }

    /**
     * Sets the record as inserted
     */
    final public function setInserted()
    {
        $this->notInserted = false;
    }

    final public function setLoaded()
    {
        $this->loaded = true;
    }

    /**
     * Returns a Query object for the current table
     *
     * @return \Aurora\Query
     */
    final public static function query()
    {
        $model = get_called_class();
        $instance = new $model();

        return new \Aurora\Query($instance->tableName, $model);
    }

    final public static function queryData($queryType = \PDO::FETCH_OBJ)
    {
        $model = get_called_class();
        $instance = new $model();

        return new \Aurora\DataQuery($instance->tableName, $queryType);
    }

    public function as_array()
    {
        $result = array();
        $columns = $this->getColumns();
        foreach ($columns as $column) {
            $result[$column->name] = $column->value;
        }

        return $result;
    }
}

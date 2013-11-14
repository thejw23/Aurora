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
namespace Aurora;

/**
 * Column
 *
 * This class represents a database column. It will be able to generate 
 * a suitable sentence for the creation of that column in SQL.
 *
 * @package Aurora
 * @author José Miguel Molina
 */
class Column
{
    /**
     * @var string The column name.
     */
    private $name;

    /**
     * @var \Aurora\Type The type of the column.
     */
    private $type;

    /**
     * @var bool Can the value be null?
     */
    private $nullable;

    /**
     * @var mixed The default value for a record.
     */
    private $default;

    /**
     * @var bool Set an unique index on this column.
     */
    private $unique;

    /**
     * @var bool Is this column a primary key?
     */
    private $primaryKey;

    /**
     * @var bool Is this column auto incremented?
     */
    private $autoIncrement;

    /**
     * @var mixed The current value of the column for a specific instance.
     */
    private $value = null;

    /**
     * @var \Aurora\ForeignKey Foreign key settings.
     */
    private $foreignKey = null;
    
    /**
     * Constructor
     *
     * @param \Aurora\Type $type The type of the column
     * @param bool $nullable If the value can be null or not
     * @param mixed $default The default value
     * @param bool $unique Set an unique index on this column or not
     * @param bool $primaryKey If the column is a primary key or not
     * @param bool $autoIncrement If the column is auto incremented or not
     * @param \Aurora\ForeignKey Foreign key settings
     * @throws \Aurora\Error\CreateTableException If the parameters are invalid
     */
    public function __construct(
        $type,
        $nullable = false,
        $default = null,
        $unique = false,
        $primaryKey = false,
        $autoIncrement = false,
        $foreignKey = null
    ) {
        try {
            $this->__set('type', $type);
            $this->__set('nullable', $nullable);
            $this->__set('unique', $unique);
            $this->__set('default', $default);
            $this->__set('primaryKey', $primaryKey);
            $this->__set('autoIncrement', $autoIncrement);
            if (!is_null($foreignKey)) {
                $this->__set('foreignKey', $foreignKey);
            }
            if (!is_null($default)) {
                $this->value = $default;
            }
        } catch (\Exception $e) {
            // If there is any kind of error with the parameters throw an error.
            throw new \Aurora\Error\CreateTableException('Invalid parameters supplied for column creation.' . $e->getMessage());
        }
    }
    
    /**
     * Get a property of the column.
     *
     * @param string $property The property
     * @return mixed
     * @throws \RuntimeException if the property does not exist
     */
    public function __get($property)
    {
        if (!in_array($property, array_keys(get_object_vars($this)))) {
            throw new \RuntimeException("{$property} property does not exist.");
        }
        return $this->$property;
    }
    
    /**
     * Set a value for a property of the column.
     *
     * @param string $property The property
     * @param mixed $value The value
     * @throws \RuntimeException if there is an error
     */
    public function __set($property, $value)
    {
        switch ($property) {
            case 'nullable':
            case 'unique':
            case 'primaryKey':
            case 'autoIncrement':
                if (!is_bool($value)) {
                    throw new \RuntimeException("{$property} only accepts boolean values.");
                }
                break;
                
            case 'type':
                if (!($value instanceof \Aurora\Type)) {
                    throw new \RuntimeException("{$property} only accepts \Aurora\Type values.");
                }
                break;
            case 'default':
            case 'value':
                if (!$this->type->isValidValue($value) && !is_null($value)) {
                    throw new \RuntimeException("The given value is not valid for the column type.");
                }
                break;
            case 'name':
                if (!is_string($value)) {
                    throw new \RuntimeException('name property only accepts string values.');
                }
                break;
            case 'foreignKey':
                if (!($value instanceof \Aurora\ForeignKey)) {
                    throw new \RuntimeException('foreignKey property only accepts \Aurora\ForeignKey values.');
                }
                break;
        }
        
        $this->$property = $value;
    }
    
    /**
     * Return the string value of the column, that is, the SQL sentence
     * @return string
     */
    public function __toString()
    {
        $isPgSQL = (\Aurora\Dbal::getDriver() instanceof \Aurora\Drivers\PostgreSQLDriver);
        
        $strValue = "{$this->name}";   
        
        /**
         * PostgreSQL uses a special type for the autoincremented columns
         * so if the driver is the PGSQL driver we should use SERIAL instead
         * of whatever type you set before.
         */
        if ($this->primaryKey 
            && $this->autoIncrement 
            && $isPgSQL) {
            $strValue .= ' SERIAL PRIMARY KEY';
        } else {
            $strValue .= " {$this->type->getRepresentation()}";
        }
        
        if (!$this->nullable) {
            $strValue .= ' NOT NULL';
        }
        
        if ($this->unique) {
            $strValue .= ' UNIQUE';
        }
        
        if (!is_null($this->default)) {
            $strValue .= " DEFAULT '{$this->default}'";
        }
        
        if ($this->primaryKey 
            && $this->autoIncrement 
            && \Aurora\Dbal::getDriver() instanceof \Aurora\Drivers\SQLiteDriver) {
            $strValue .= ' PRIMARY KEY';
        }

        if ($this->autoIncrement && !$isPgSQL) {
            $strValue .= (!(\Aurora\Dbal::getDriver() instanceof \Aurora\Drivers\SQLiteDriver)) ? ' AUTO_INCREMENT' : ' AUTOINCREMENT';
        }
            
        return $strValue;
    }
}

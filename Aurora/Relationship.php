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
 * Relationship
 *
 * This query defines a relationship between two models.
 *
 * @package Aurora
 * @author José Miguel Molina
 */
class Relationship
{
    /**
     * @var string The foreign key of the table where you set that relationship
     */
    private $foreignKey;

    /**
     * @var string The name of the model used in the relationship
     */
    private $model;

    /**
     * @var string The field of the model linked to the foreign key
     */
    private $modelField;

    /**
     * @var string If the relationship can contains a single value or not
     */
    private $single;

    /**
     * @var string The value of the relationship (those records whose model field equals the foreign key)
     */
    private $value = null;

    /**
     * Constructor
     *
     * @param string $model The name of the model used in the relationship
     * @param string $modelField The field of the model linked to the foreign key
     * @param string $foreignKey The foreign key of the table where you set that relationship
     * @param bool $single If the relationship can contains a single value or not
     */
    final public function __construct($model, $modelField, $foreignKey, $single = true)
    {
        $this->model = $model;
        $this->modelField = $modelField;
        $this->foreignKey = $foreignKey;
        $this->single = $single;
    }

    /**
     * Checks for the existance of the property
     *
     * @param string $property The property
     * @return bool
     */
    final public function __isset($property)
    {
        return in_array($property, array_keys(get_object_vars($this)));
    }

    /**
     * Returns the value of a property
     *
     * @param string $property The property
     * @return mixed
     * @throws \RuntimeException If the property does not exist
     */
    public final function __get($property)
    {
        if ($this->__isset($property)) {
            return $this->$property;
        } else {
            throw new \RuntimeException($property . ' property does not exist.');
        }
    }
    
    /**
     * Returns the foreign key
     *
     * @return string
     */
    final public function getForeignKey()
    {
        return $this->foreignKey;
    }

    /**
     * Retrieves the values of the relationship (those records whose model field equals the foreign key)
     *
     * @param mixed $fkValue The value of the foreign key
     */
    final public function retrieve($fkValue)
    {
        $model = $this->model;
        $query = $model::query()->filterBy(array($this->modelField, $fkValue));
        if ($this->single) {
            $this->value = $query->first();
            if ($this->value === false)
                $this->value = $model::instance();
        } else {
            $this->value = $query->all();
        }
    }
}

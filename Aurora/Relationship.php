<?php
/**
 * Aurora - Fast and easy to use php ORM.
 *
 * @author      José Miguel Molina <hi@mvader.me>
 * @copyright   2013 José Miguel Molina
 * @link        https://github.com/mvader/Aurora
 * @license     https://raw.github.com/mvader/Aurora/master/LICENSE
 * @version     1.0.0
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

class Relationship
{
    private $foreignKey;
    private $model;
    private $modelField;
    private $single;
    private $value = null;

    public final function __construct($model, $modelField, $foreignKey, $single = true)
    {
        $this->model = $model;
        $this->modelField = $modelField;
        $this->foreignKey = $foreignKey;
        $this->single = $single;
    }

    public final function __isset($property)
    {
        return in_array($property, array_keys(get_object_vars($this)));
    }

    public final function __get($property)
    {
        if ($this->__isset($property))
            return $this->$property;
        else
            throw new \RuntimeException("{$property} property does not exist.");
    }
    
    public final function getForeignKey()
    {
        return $this->foreignKey;
    }

    public final function retrieve($fkValue)
    {
        $model = $this->model;
        $query = $model::query()->filterBy(array($this->modelField, $fkValue));
        if ($this->single) {
            $this->value = $query->first();
            if ($this->value === false)
                $this->value = $model::instance();
        } else
            $this->value = $query->all();
    }
}
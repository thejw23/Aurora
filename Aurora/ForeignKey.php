<?php
/**
 * Aurora - Fast and easy to use php ORM.
 *
 * @author      José Miguel Molina <hi@mvader.me>
 * @copyright   2013 José Miguel Molina
 * @link        https://github.com/mvader/Aurora
 * @license     https://raw.github.com/mvader/Aurora/master/LICENSE
 * @version     1.0.2
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
 * ForeignKey
 *
 * This class is used to set a foreign key on the column.
 *
 * @package Aurora
 * @author José Miguel Molina
 */
class ForeignKey
{
    /**
     * @var string $tableName The name of the referenced table.
     */
    private $tableName;

    /**
     * @var string $fieldName The name of the referenced field.
     */
    private $fieldName;

    /**
     * @var string $selfField The name of the field to be used as foreign key.
     */
    private $selfField;

    /**
     * Constructor
     *
     * @param string $model The referenced model. This is the name of the class that represents the table you're referencing.
     * @param string $modelField The referenced field of the model.
     * @param string $field The name of the field to be used as foreign key.
     * @param string $onDelete Action to perform on delete in the referenced table.
     * @param string $onUpdate Action to perform on update in the referenced table.
     * @throws \RuntimeException If the class or the field don't exist.
     */
    public final function __construct(
        $model,
        $modelField,
        $field,
        $onDelete = 'NO ACTION',
        $onUpdate = 'NO ACTION'
    ) {
        if (!class_exists($model)) {
            throw new \RuntimeException($model . ' class does not exist.');
        }

        $instance = new $model();
        if (!isset($instance->$modelField)) {
            throw new \RuntimeException($modelField . ' property not found for ' . $model);
        }

        $this->fieldName = $modelField;
        $this->tableName = $instance->getName();
        $this->selfField = $field;
        $validActions = array('NO ACTION', 'SET NULL', 'CASCADE');
        if (!in_array($onDelete, $validActions)) {
            throw new \RuntimeException('Invalid action ' . $onDelete . ' for ON DELETE.');
        }
        if (!in_array($onUpdate, $validActions)) {
            throw new \RuntimeException('Invalid action ' . $onDelete . ' for ON UPDATE.');
        }

        $this->onUpdate = $onUpdate;
        $this->onDelete = $onDelete;
    }

    /**
     * Returns the string representation of the foreign key
     *
     * @return string
     */
    public final function __toString()
    {
        return 'FOREIGN KEY (' . $this->selfField . ') REFERENCES ' .
                $this->tableName . '(' . $this->fieldName . ') ON UPDATE ' .
                $this->onUpdate . ' ON DELETE ' . $this->onDelete;
    }
}

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

/**
 * Type
 *
 * Abstract base class for the data types.
 *
 * @package Aurora
 * @author José Miguel Molina
 */
abstract class Type
{

    /**
     * Get the type representation e.g. INTEGER, VARCHAR, ...
     *
     * @return string
     */
    abstract public function getRepresentation();

    /**
     * Check if a value is valid for this type
     *
     * @param mixed $value The value
     * @return bool
     */
    abstract public function isValidValue($value);

    /**
     * Parse a value before inserting it into the database
     *
     * @param mixed $value The value
     * @return mixed The parsed value
     */
    abstract public function parseValue($value);
	
    /**
     * Get the actual driver used by the database abstraction layer
     *
     * @return \Aurora\Drivers\BaseDriver The driver.
     * @throws \Aurora\Error\DatabaseException if the driver is not configured
     */
    final public function getDriver() {
        $driver = \Aurora\Dbal::getDriver();
        if (is_null($driver))
            throw new \Aurora\Error\DatabaseException('Database driver must be configured before the creation of any \Aurora\Type instance.');
        return $driver;
    }
}
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
namespace Aurora\Types;

/**
 * String
 *
 * String data type.
 *
 * @package Aurora
 * @author José Miguel Molina
 */
class String extends \Aurora\Type
{
    /**
     * @var int Maximum number of characters
     */
    private $size;

    /**
     * Constructor
     *
     * @param int $size Maximum number of characters
     */
    public function __construct($size = 255)
    {
        $this->size = $size;
    }

    /**
     * Check if a value is valid for this type
     *
     * @param  mixed $value The value
     * @return bool
     */
    public function isValidValue($value)
    {
        return is_string($value);
    }

    /**
     * Get the type representation e.g. INTEGER, VARCHAR, ...
     *
     * @return string
     */
    public function getRepresentation()
    {
        $driver = $this->getDriver();

        if (!($driver instanceof \Aurora\Drivers\SQLiteDriver)) {
            return 'VARCHAR(' . $this->size . ')';
        }

        return 'TEXT';
    }

    /**
     * Parse a value before inserting it into the database
     *
     * @param  mixed $value The value
     * @return mixed The parsed value
     */
    public function parseValue($value)
    {
        if (is_null($value)) {
            return NULL;
        } else {
            return (string) $value;
        }
    }
}

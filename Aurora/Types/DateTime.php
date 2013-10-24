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
namespace Aurora\Types;

class DateTime extends \Aurora\Type
{
 
    public function __construct()
    {
    }
    
    public function isValidValue($value)
    {
        if ($value instanceof \DateTime)
            return true;
        elseif (is_string($value)) {
            try {
                $date = new \DateTime($value);
            } catch (\Exception $e) {
                return false;
            }
            
            return true;
        }
        
        return false;
    }
    
    public function getRepresentation()
    {
        $driver = $this->getDriver();
        
        if (!($driver instanceof \Aurora\Drivers\SQLiteDriver)) {
            return ($driver instanceof \Aurora\Drivers\MySQLDriver) 
                ? "DATETIME" : "TIMESTAMP";
        }
        return 'INTEGER';
    }
    
    public function retrieveValue($value)
    {
        if (is_string($value)) {
            try {
                return new \DateTime($value);
            } catch (\Exception $e) {
                // Epoch (retrieved from SQLite)
                try {
                    return new \DateTime("@$value");
                } catch (\Exception $e) {
                    return false;
                }
            }
        } else {
            return $value;
        }
    }
    
    public function parseValue($value)
    {
        $value = $this->retrieveValue($value);
        
        if (!$value)
            throw new \RuntimeException("The given value is not a valid \Aurora\Types\DateTime value.");
        
        $driver = $this->getDriver();
        if (!($driver instanceof \Aurora\Drivers\SQLiteDriver)) {
            return (string) $value->format("Y-m-d H:i:s");
        }
        return (int) $value->format('U');
    }
}
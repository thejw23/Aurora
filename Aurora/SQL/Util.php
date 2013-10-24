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
namespace Aurora\SQL;

/**
 * Util
 *
 * This class provides some SQL utilities.
 *
 * @package Aurora
 * @author José Miguel Molina
 */
class Util
{
    /**
     * @var array The allowed SQL operators
     */
    private static $allowedOperators = array(
        'AND',
        'OR',
        '>',
        '<',
        '>=',
        '<=',
        '<>',
        'LIKE',
        'IN'
    );
    
    /**
     * Returns a string to check that all the fields passed equal certain values
     *
     * @param array $fields The fields
     * @return string
     */
    public static function andEqualColumns(array $fields)
    {
        return join(' AND ', array_map(
            function($field) {
                $key = ($field instanceof \Aurora\Column) ? $field->name :
                    $field;
                return "{$key} = ?";
            },
            $fields
        ));
    }
    
    /**
     * Converts a clause from array to string
     *
     * @param array $args The clause
     * @param array $params The reference of the var where the params will be stored
     * @return string
     * @throws \RuntimeException If there is any error
     */
    public static function clauseReduce(array $args, array &$params)
    {
        if (count($args) < 2 || count($args) > 3)
            throw new \RuntimeException("Invalid number of parameters for clause.");
        
        if (count($args) == 2) {
            $params[] = $args[1];
            return "{$args[0]} = ?";
        } else {
            if (!in_array($args[1], self::$allowedOperators))
                throw new \RuntimeException("{$args[1]} is not a valid SQL operator.");
            
            if (is_array($args[0]))
                $a = self::clauseReduce($args[0], $params);
            else
                $a = $args[0];
            
            if ($args[1] == 'IN') {
                if (!is_array($args[2]))
                    throw new \RuntimeException("Expected an array after an IN operator.");
                
                $b = '('. join(', ', array_map(
                        function($item) use (&$params) {
                            $params[] = $item;
                            return '?';
                        },
                        $args[2]
                    )) .')';
            } else {
                if (is_array($args[2]))
                    $b = self::clauseReduce($args[2], $params);
                else {
                    $params[] = $args[2];
                    $b = '?';
                }
            }
            
            return "{$a} {$args[1]} {$b}";
        }
    }
}
<?php

namespace Aurora\SQL;

class Util
{
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
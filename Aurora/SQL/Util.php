<?php

namespace Aurora\SQL;

class Util
{
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
}
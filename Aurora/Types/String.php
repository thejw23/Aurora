<?php

namespace Aurora\Types;

class String extends \Aurora\Type
{
    private $size;
    
    public function __construct($size = 255)
    {
        $this->size = $size;
    }
    
    public function isValidValue($value)
    {
        return is_string($value);
    }
    
    public function getRepresentation()
    {
        $driver = $this->getDriver();
        
        if (!($driver instanceof \Aurora\Drivers\SQLiteDriver)) {
            return "VARCHAR({$this->size})";
        }
        return 'TEXT';
    }
    
    public function parseValue($value)
    {
        return (string) $value;
    }
}
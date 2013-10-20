<?php

namespace Aurora\Types;

class Decimal extends \Aurora\Type
{
    private $length;
    private $decimals;
    
    public function __construct($length, $decimals)
    {
        $this->length = (int) $length;
        $this->decimals = (int) $decimals;
    }
    
    public function isValidValue($value)
    {
        return is_float($value);
    }
    
    public function getRepresentation()
    {
        $driver = $this->getDriver();
        
        if (!($driver instanceof \Aurora\Drivers\SQLiteDriver)) {
            return "DECIMAL({$this->length},{$this->decimals})";
        }
        return 'REAL';
    }
    
    public function parseValue($value)
    {
        return (float) $value;
    }
}
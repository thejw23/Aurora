<?php

namespace Aurora\Types;

class Blob extends \Aurora\Type
{
    public function isValidValue($value)
    {
        return true;
    }
    
    public function getRepresentation()
    {
        $driver = $this->getDriver();
        
        return 'BLOB';
    }
    
    public function parseValue($value)
    {
        return $value;
    }
}
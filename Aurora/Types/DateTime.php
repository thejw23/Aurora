<?php

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
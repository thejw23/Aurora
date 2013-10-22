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
            return "DATETIME";
        }
        return 'INTEGER';
    }
    
    public function parseValue($value)
    {
        if (is_string($value)) {
            try {
                $value = new \DateTime($value);
            } catch (\Exception $e) {
                // Epoch (retrieved from SQLite)
                try {
                    $value = new \DateTime("@$value");
                } catch (\Exception $e) {
                    return false;
                }
            }
        }
        
        $driver = $this->getDriver();
        if (!($driver instanceof \Aurora\Drivers\SQLiteDriver)) {
            return $value->format("Y-m-d H:i:s");
        }
        return (int) $value->format('U');
    }
}
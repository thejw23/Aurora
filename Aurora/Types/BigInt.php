<?php

namespace Aurora\Types;

class BigInt extends \Aurora\Types\Int
{
    public function getRepresentation()
    {
        $driver = $this->getDriver();
        
        if (!($driver instanceof \Aurora\Drivers\SQLiteDriver)) {
            return 'BIGINT' . (($this->unsigned) ? 'UNSIGNED' : '');
        }
        return 'INTEGER';
    }
}
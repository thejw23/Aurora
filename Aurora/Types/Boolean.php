<?php

namespace Aurora\Types;

class Boolean extends \Aurora\Types\Int
{
	public function getRepresentation()
	{
		$driver = $this->getDriver();
		
		if (!($driver instanceof \Aurora\Drivers\SQLiteDriver)) {
			return 'TINYINT UNSIGNED');
		}
		return 'INTEGER';
	}
    
    public function parseValue($value)
    {
        return (int) $value;
    }
}
<?php

namespace Aurora\Types;

class Int extends \Aurora\Type
{
	private $unsigned;
	
	public function __construct($unsigned = false)
	{
		$this->unsigned = $unsigned;
	}
	
	public function isValidValue($value)
	{
		return !(!is_int($value) || ($this->unsigned && $value < 0));
	}
	
	public function getRepresentation()
	{
		$driver = $this->getDriver();
		
		if (!($driver instanceof \Aurora\Drivers\SQLiteDriver) &&
			$this->unsigned) {
			return 'INTEGER UNSIGNED';
		}
		return 'INTEGER';
	}
    
    public function parseValue($value)
    {
        return (int) $value;
    }
}
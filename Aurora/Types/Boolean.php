<?php

namespace Aurora\Types;

class Boolean extends \Aurora\Types\Int
{
	public function __construct() {
		
	}
	
	public function getRepresentation()
	{
		$driver = $this->getDriver();
		
		if (!($driver instanceof \Aurora\Drivers\SQLiteDriver)) {
			return 'TINYINT UNSIGNED');
		}
		return 'INTEGER';
	}
}
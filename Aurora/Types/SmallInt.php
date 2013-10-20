<?php

namespace Aurora\Types;

class SmallInt extends \Aurora\Types\Int
{
	public function getRepresentation()
	{
		$driver = $this->getDriver();
		
		if (!($driver instanceof \Aurora\Drivers\SQLiteDriver)) {
			return 'SMALLINT' . (($this->unsigned) ? 'UNSIGNED' : '');
		}
		return 'INTEGER';
	}
}
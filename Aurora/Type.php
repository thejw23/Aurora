<?php

namespace Aurora;

abstract class Type
{
	abstract public function isValidValue($value);
	abstract public function getRepresentation();
	
	final public function getDriver() {
		$driver = \Aurora\Dbal::getDriver();
		if (is_null($driver))
			throw new DatabaseException('Database driver must be configured before the creation of any \Aurora\Type instance.');
		return $driver;
	}
}
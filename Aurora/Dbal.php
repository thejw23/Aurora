<?php

namespace Aurora;

class Dbal
{
	private static $driver = null;
	private static $conn = null;
	
	public static function init(\Aurora\Drivers\BaseDriver $dbDriver)
	{
		self::$driver = $dbDriver;
	}
	
	private static function connect()
	{
		try {
			self::$conn = self::$driver->getConnection();
			self::$conn->setAttribute(
				\PDO::ATTR_ERRMODE,
				\PDO::ERRMODE_EXCEPTION
			);
		} catch (\PDOException $e) {
			throw new \Aurora\Error\DatabaseException('Unable to establish database connection.');
		}
	}
	
	public static function query($sentence, $params, $return = true)
	{
		if (is_null(self::$conn))
			self::connect();
		
		if (!(is_null($params) || is_array($params)))
			throw new \RuntimeException("\Aurora\Dbal::query argument $params MUST be an array or null.");
		
		try {
			if (!$return)
				self::$conn->beginTransaction();
			$stmt = self::$conn->prepare($sentence);
			$result = (is_null($params)) ? $stmt->execute() :
				$stmt->execute($params);
			if ($result) {
				if (!$return) {
					self::$conn->commit();
					$stmt = null;
				}
				
				return (($return) ? $stmt : true);
			} else {
				self::$conn->rollBack();
				return false;
			}
		} catch (\PDOException $e) {
            if (!$return)
			    self::$conn->rollBack();
			throw new \Aurora\Error\DatabaseException("Query error: " . $e->getMessage());
		}
	}
	
	public static function getDriver()
	{
		return self::$driver;
	}
}
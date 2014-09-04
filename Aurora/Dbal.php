<?php
/**
 * Aurora - Fast and easy to use php ORM.
 *
 * @author      José Miguel Molina <hi@mvader.me>
 * @copyright   2013 José Miguel Molina
 * @link        https://github.com/mvader/Aurora
 * @license     https://raw.github.com/mvader/Aurora/master/LICENSE
 * @version     1.0.3
 * @package     Aurora
 *
 * MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */
namespace Aurora;

/**
 * Dbal
 *
 * This is the database abstraction layer used to perform queries.
 * This class only uses static methods, no instance of it is needed.
 *
 * @package Aurora
 * @author José Miguel Molina
 */
class Dbal
{
    /**
     * @var \Aurora\Drivers\BaseDriver The driver used for connection
     */
    private static $driver = null;

    /**
     * @var \PDO PDO connection
     */
    private static $conn = null;

    public static $profiling = FALSE;

    /**
     * Constructor
     *
     * Nope, you won't be able to instantiate this class.
     */
    final private function __construct()
    {
        // Here be dragons
    }

    /**
     * Sets the driver used for the connection
     *
     * @param \Aurora\Drivers\BaseDriver $dbDriver The driver
     */
    final public static function init(\Aurora\Drivers\BaseDriver $dbDriver)
    {
        self::$driver = $dbDriver;
    }

    /**
     * Gets the PDO connection object
     *
     * @throws \Aurora\Error\DatabaseException If PDO is unable to establish connection
     */
    final private static function connect()
    {
        try {
            self::$conn = self::$driver->getConnection();
            self::$conn->setAttribute(
                \PDO::ATTR_ERRMODE,
                \PDO::ERRMODE_EXCEPTION
            );
            self::$conn->setAttribute(\PDO::ATTR_STATEMENT_CLASS, array("\\Aurora\\PDO\\E_PDOStatement", array(self::$conn)));
        } catch (\PDOException $e) {
            throw new \RuntimeException('Unable to establish database connection.' . $e->getMessage());
        }
    }

    /**
     * Performs a query on the database
     *
     * @param  string                      $sentence   The SQL query sentece
     * @param  array                       $params     The parameters passed to the query
     * @param  bool                        $return     Does the query return anything?
     * @param  mixed                       $insertedId Reference to a var where the last inserted id will be stored if applicable
     * @param  string                      $name       The name of the constraint to get the last inserted id. That is only needed in PostgreSQL
     * @return \PDOStatement|null
     * @throws \RuntimeException           If $params is not array or null
     * @throws \Aurora\Error\DatabaseError If there is an error with the query
     */
    final public static function query(
        $sentence,
        $params = null,
        $return = true,
        &$insertedId = null,
        $name = null
    ) {
        // Connect only if we're not already connected to the database
        if (is_null(self::$conn)) {
            self::connect();
        }

        // Parameters MUST be array or null
        if (!(is_null($params) || is_array($params))) {
            throw new \RuntimeException('\Aurora\Dbal::query argument $params MUST be an array or null.');
        }

        try {
            // If it is an update, insert or delete query begin transaction
            if (!$return) {
                self::$conn->beginTransaction();
            }

            // Prepare the query and execute it
            $stmt = self::$conn->prepare($sentence);
        
            if (static::$profiling === TRUE)
            {
                \Aurora\Profiler::start();
            }


            $result = (is_null($params)) ? $stmt->execute() :
                $stmt->execute($params);
          
            if (static::$profiling === TRUE)
            {
                \Aurora\Profiler::stop($stmt->fullQuery);
            }                


            if ($result) {
                // Get the last inserted id if it is a select query
                if (!$return) {
                    $insertedId = self::$conn->lastInsertId($name);
                    self::$conn->commit();
                    $stmt = null;
                }

                return (($return) ? $stmt : true);
            }
        } catch (\PDOException $e) {
            // Roll back if there is an error
            if (!$return) {
                self::$conn->rollBack();
            }
            throw new \RuntimeException('Query error: ' . $e->getMessage());
        }
    }

    /**
     * Returns the driver used to connect to de database
     *
     * @return \Aurora\Driver\BaseDriver
     */
    final public static function getDriver()
    {
        return self::$driver;
    }
}

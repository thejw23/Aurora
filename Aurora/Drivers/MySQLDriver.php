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
namespace Aurora\Drivers;

/**
 * MySQLDriver
 *
 * Driver to connect to MySQL.
 *
 * @package Aurora
 * @author José Miguel Molina
 */
class MySQLDriver implements \Aurora\Drivers\BaseDriver
{
    /**
     * @var string Database host
     */
    private $host;

    /**
     * @var string Database name
     */
    private $dbname;

    /**
     * @var int|string Database port
     */
    private $port;

    /**
     * @var string Database user
     */
    private $user;

    /**
     * @var string Database password
     */
    private $password;

    /**
     * @var string Connection encoding
     */
    private $encoding;

    /**
     * Constructor
     *
     * @param string     $host     The database host
     * @param string     $dbname   The database name
     * @param int|string $port     The database port
     * @param string     $user     The database user
     * @param string     $password The database password
     */
    public function __construct($host, $dbname, $port, $user, $password, $encoding = 'UTF8')
    {
        $this->host = $host;
        $this->dbname = $dbname;
        $this->port = (int) $port;
        $this->user = $user;
        $this->password = $password;
        $this->encoding = $encoding;
    }

    /**
     * Returns the connection string to use with PDO
     *
     * @return string
     */
    public function getConnection()
    {
        $pdo = new \PDO(
            'mysql:host=' . $this->host . ';dbname=' . $this->dbname .
            ';port=' . $this->port,
            $this->user,
            $this->password,
            array(\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
        );
        return $pdo;
    }
}

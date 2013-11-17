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
 * Query
 *
 * This class is used to perform queries on the database.
 * The queries in Aurora have two parts. You can call any method you want
 * whenever you want but the query will not be performed untill you call
 * a limit operation method (all, limit, first or get). Those methods are
 * the end of query. The rest of the methods will just append fields to
 * the query.
 *
 * @package Aurora
 * @author José Miguel Molina
 */
class Query
{
    /** 
     * @var array The query parts
     */
    private $query = array(
        'fields'            => '*',
        'table'             => '',
    );

    /**
     * @var string The model to use
     */
    private $model;

    /**
     * @var array The parameters of the query
     */
    private $params = array();
    
    /**
     * Constructor
     *
     * @param string The table to query
     * @param string The model to use
     */
    final public function __construct($table, $model)
    {
        $this->query['table'] = $table;
        $this->model = $model;
    }
    
    /**
     * Returns all the records for that query
     *
     * @return array
     */
    final public function all()
    {
        if (isset($this->query['limit'])) {
            unset($this->query['limit']);
        }
        
        return self::getResults(
            $this->model,
            self::buildQuery($this->query),
            $this->params
        );
    }
    
    /**
     * Returns the first record for that query
     *
     * @return \Aurora\Table
     */
    final public function first()
    {
        return $this->limit(0, 1);
    }
    
    /**
     * Returns the record at the specified position for that query
     *
     * @return \Aurora\Table
     */
    final public function get($number)
    {
        return $this->limit($number, 1);
    }
    
    /**
     * Returns the number of records with the specified offset for that query
     *
     * @return \Aurora\Table
     */
    final public function limit($offset, $num = false)
    {
        if (\Aurora\Dbal::getDriver() 
            instanceof \Aurora\Drivers\PostgreSQLDriver) {
            if ($num === false) {
                $this->query['limit'] = (int) $offset;
            } else {
                $this->query['limit'] = ((int) $num) . ' OFFSET ' 
                    . ((int) $offset);
            }
        } else {
            if ($num === false) {
                $this->query['limit'] = (int) $offset;
            } else {
                $this->query['limit'] = $offset . ', ' . $num;
            }
        }
        
        $results = self::getResults(
            $this->model,
            self::buildQuery($this->query),
            $this->params
        );
        
        if ($num == 1) {
            if (count($results) == 1) {
                return $results[0];
            } else {
                return false;
            }
        } else {
            return $results;
        }
    }
    
    /**
     * Adds a where clause to filter based on the array parameter
     *
     * @param array $args The filter arguments
     * @return \Aurora\Query
     */
    final public function filterBy(array $args)
    {
        $params = array();
        $where = \Aurora\SQL\Util::clauseReduce($args, $params);
        $this->query['where'] = $where;
        $this->params = array_merge($this->params, $params);
        
        return $this;
    }
    
    /**
     * Adds a manual where clause
     *
     * @param string $clause The where clause
     * @param array $params The parameters
     * @return \Aurora\Query
     */
    final public function where($clause, array $params = array())
    {
        $this->query['where'] = $clause;
        if (count($params) > 0) {
            $this->params = array_merge($this->params, $params);
        }
        return $this;
    }
    
    /**
     * Adds an order by clause
     *
     * @param array|string $field The fields to order by
     * @param string $order The order ASC or DESC
     * @return \Aurora\Query
     */
    final public function orderBy($field, $order = 'ASC')
    {
        if ($order != 'ASC' && $order != 'DESC') {
            throw new \RuntimeException('Second parameter of \Aurora\Query::orderBy MUST be ASC or DESC.');
        }
        
        if (is_array($field)) {
            $field = join(', ', $field);
        }
        
        $this->query['order_by'] = $field . ' ' . $order;
        return $this;
    }
    
    /**
     * Returns the SQL sentence for the query
     *
     * @return string
     */
    final private static function buildQuery($query)
    {
        $sql = 'SELECT ' . $query['fields'] . ' FROM ' .
                $query['table'];
        
        if (isset($query['where'])) {
            $sql .= ' WHERE ' . $query['where'];
        }
        
        if (isset($query['order_by'])) {
            $sql .= ' ORDER BY ' . $query['order_by'];
        }
        
        if (isset($query['limit'])) {
            $sql .= ' LIMIT ' . $query['limit'];
        }
        
        return $sql;
    }
    
    /**
     * Performs the query and returns its results
     *
     * @param string $model The model
     * @param string $sql The sql sentence
     * @param array $params The parameters
     */
    final private static function getResults($model, $sql, $params = null)
    {
        if (count($params) == 0) {
            $params = null;
        }
        
        $rows = \Aurora\Dbal::query($sql, $params);
        $results = array();
        while ($row = $rows->fetch(\PDO::FETCH_ASSOC)) {
            $result = new $model();
            foreach ($row as $key => $val) {
                $result->$key = $result->parseValue($key, $val);
                $result->setInserted();
            }
            $results[] = $result;
        }
        $rows = null;
        
        return $results;
    }
}

<?php

namespace Aurora;

class Query
{
    private $query = array(
        'fields'            => '*',
        'table'             => '',
    );
    private $model;
    private $params = array();
    
    final public function __construct($table, $model)
    {
        $this->query['table'] = $table;
        $this->model = $model;
    }
    
    final public function all()
    {
        if (isset($this->query['limit']))
            unset($this->query['limit']);
        
        return self::getResults(
            $this->model,
            self::buildQuery($this->query),
            $this->params
        );
    }
    
    final public function first()
    {
        return $this->limit(0, 1);
    }
    
    final public function get($number)
    {
        return $this->limit($number, 1);
    }
    
    final public function limit($offset, $num = false)
    {
        if ($num === false)
            $this->query['limit'] = (int) $offset;
        else
            $this->query['limit'] = $offset . ', ' . $num;
        
        $results = self::getResults(
            $this->model,
            self::buildQuery($this->query),
            $this->params
        );
        
        if ($num == 1) {
            if (count($results) == 1)
                return $results[0];
            else
                return false;
        } else {
            return $results;
        }
    }
    
    public final function filterBy(array $args)
    {
        $params = array();
        $where = \Aurora\SQL\Util::clauseReduce($args, $params);
        $this->query['where'] = $where;
        $this->params = array_merge($this->params, $params);
        
        return $this;
    }
    
    public final function where($clause, array $params = array())
    {
        $this->query['where'] = $clause;
        if (count($params) > 0)
            $this->params = array_merge($this->params, $params);
        return $this;
    }
    
    public final function orderBy($field, $order = 'ASC')
    {
        if ($order != 'ASC' && $order != 'DESC')
            throw new \RuntimeException('Second parameter of \Aurora\Query::orderBy MUST be ASC or DESC.');
        
        if (is_array($field))
            $field = join(', ', $field);
        
        $this->query['order_by'] = "{$field} {$order}";
        return $this;
    }
    
    private final static function buildQuery($query)
    {
        $sql = "SELECT {$query['fields']} FROM " . 
            "{$query['table']}";
        
        if (isset($query['where']))
            $sql .= ' WHERE ' . $query['where'];
        
        if (isset($query['order_by']))
            $sql .= ' ORDER BY ' . $query['order_by'];
        
        if (isset($query['limit']))
            $sql .= ' LIMIT ' . $query['limit'];
        
        return $sql;
    }
    
    private final static function getResults($model, $sql, $params = null)
    {
        if (count($params) == 0)
            $params = null;
        
        $rows = \Aurora\Dbal::query($sql, $params);
        $results = array();
        while ($row = $rows->fetch(\PDO::FETCH_ASSOC)) {
            $result = $model::instance();
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
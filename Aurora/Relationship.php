<?php

namespace Aurora;

class Relationship
{
    private $foreignKey;
    private $model;
    private $modelField;
    private $single;
    private $value = null;

    public final function __construct($model, $modelField, $foreignKey, $single = true)
    {
        $this->model = $model;
        $this->modelField = $modelField;
        $this->foreignKey = $foreignKey;
        $this->single = $single;
    }

    public final function __isset($property)
    {
        return in_array($property, array_keys(get_object_vars($this)));
    }

    public final function __get($property)
    {
        if ($this->__isset($property))
            return $this->$property;
        else
            throw new \RuntimeException("{$property} property does not exist.");
    }
    
    public final function getForeignKey()
    {
        return $this->foreignKey;
    }

    public final function retrieve($fkValue)
    {
        $model = $this->model;
        $query = $model::query()->filterBy(array($this->modelField, $fkValue));
        if ($this->single) {
            $this->value = $query->first();
            if ($this->value === false)
                $this->value = $model::instance();
        } else
            $this->value = $query->all();
    }
}
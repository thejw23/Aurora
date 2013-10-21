<?php

namespace Aurora;

class ForeignKey
{
    private $tableName;
    private $fieldName;
    private $selfField;

    public final function __construct(
        $model,
        $modelField,
        $field,
        $onDelete = 'NO ACTION',
        $onUpdate = 'NO ACTION'
    ) {
        if (!class_exists($model))
            throw new \RuntimeException("{$model} class does not exist.");

        $instance = $model::instance();
        if (!isset($instance->$modelField))
            throw new \RuntimeException("{$modelField} property not found for {$model}.");

        $this->fieldName = $modelField;
        $this->tableName = $instance->getName();
        $this->selfField = $field;
        $validActions = array('NO ACTION', 'SET NULL', 'CASCADE');
        if (!in_array($onDelete, $validActions))
            throw new \RuntimeException("Invalid action {$onDelete} for ON DELETE.");
        if (!in_array($onUpdate, $validActions))
            throw new \RuntimeException("Invalid action {$onUpdate} for ON UPDATE.");

        $this->onUpdate = $onUpdate;
        $this->onDelete = $onDelete;
    }

    public final function __toString()
    {
        return "FOREIGN KEY ({$this->selfField}) REFERENCES {$this->tableName}" . 
                "({$this->fieldName}} ON UPDATE {$this->onUpdate} ON UPDATE " . 
                "{$this->onDelete})";
    }
}
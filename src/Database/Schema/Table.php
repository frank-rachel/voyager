<?php
namespace TCG\Voyager\Database\Schema;

class Table
{
    public $name;
    public $columns;
    public $indexes;
    public $foreignKeys;
    public $options;

    public function __construct($name, array $columns = [], array $indexes = [], array $foreignKeys = [], array $options = [])
    {
        $this->name = $name;
        $this->columns = $columns; // columns should be an array of Column objects
        $this->indexes = $indexes;
        $this->foreignKeys = $foreignKeys;
        $this->options = $options;
    }

    public function addColumn(Column $column)
    {
        $this->columns[$column->getName()] = $column;
    }

    public function removeColumn($columnName)
    {
        unset($this->columns[$columnName]);
    }

    public function getColumn($columnName)
    {
        return $this->columns[$columnName] ?? null;
    }

    public function getColumns()
    {
        return $this->columns;
    }

    public function toArray()
    {
        return [
            'name' => $this->name,
            'columns' => array_map(function ($column) { return $column->toArray(); }, $this->columns),
            'indexes' => $this->indexes, // Assume indexes are handled similarly
            'foreignKeys' => $this->foreignKeys, // Assume foreign keys are handled similarly
            'options' => $this->options,
        ];
    }

    public function toJson()
    {
        return json_encode($this->toArray());
    }

    public function getName()
    {
        return $this->name;
    }

    public function getIndexes()
    {
        return $this->indexes;
    }

    public function getForeignKeys()
    {
        return $this->foreignKeys;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public static function make($table)
    {
        if (!is_array($table)) {
            $table = json_decode($table, true);
        }

        $name = Identifier::validate($table['name'], 'Table');

        return new self(
            $name,
            Column::makeMany($table['columns']),
            Index::makeMany($table['indexes']),
            ForeignKey::makeMany($table['foreignKeys']),
            $table['options'] ?? []
        );
    }

    public function getColumnsIndexes($columns, $sort = false)
    {
        if (!is_array($columns)) {
            $columns = [$columns];
        }

        $matched = [];
        foreach ($this->indexes as $index) {
            if ($index->spansColumns($columns)) {
                $matched[$index->getName()] = $index;
            }
        }

        if ($sort && count($matched) > 1) {
            uasort($matched, [$this, 'compareIndexPriority']);
        }

        return $matched;
    }

    protected function compareIndexPriority($index1, $index2)
    {
        $priorities = ['PRIMARY' => 3, 'UNIQUE' => 2, 'INDEX' => 1];
        $priority1 = $priorities[$index1->getType()] ?? 0;
        $priority2 = $priorities[$index2->getType()] ?? 0;

        return $priority2 <=> $priority1; // Using PHP 7 spaceship operator for comparison
    }

    public function diff(Table $compareTable)
    {
        // Custom logic to compare this table with another table
        $differences = [];
        if ($this->name !== $compareTable->name) {
            $differences['name'] = "Different names: {$this->name} vs {$compareTable->name}";
        }
        // More detailed comparison can be added here
        return $differences;
    }

    public function diffOriginal()
    {
        return (new Comparator())->diffTable(SchemaManager::getDoctrineTable($this->_name), $this);
    }

    public function exportColumnsToArray()
    {
        return array_map(function($column) {
            return $column->toArray();
        }, $this->columns);
    }

    public function exportIndexesToArray()
    {
        return array_map(function($index) {
            return $index->toArray() + ['table' => $this->name];
        }, $this->indexes);
    }

    public function exportForeignKeysToArray()
    {
        return array_map(function($fk) {
            return $fk->toArray();
        }, $this->foreignKeys);
    }
}

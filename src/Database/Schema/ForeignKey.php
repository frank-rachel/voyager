<?php

namespace TCG\Voyager\Database\Schema;

class ForeignKey
{
    public $name;
    public $localColumns;
    public $foreignTable;
    public $foreignColumns;
    public $options;

    public function __construct($name, array $localColumns, $foreignTable, array $foreignColumns, array $options = [])
    {
        $this->name = $name;
        $this->localColumns = $localColumns;
        $this->foreignTable = $foreignTable;
        $this->foreignColumns = $foreignColumns;
        $this->options = $options;
    }

    public static function make(array $foreignKey)
    {
        $name = $foreignKey['name'] ?? null;
        $localColumns = $foreignKey['localColumns'];
        $foreignTable = $foreignKey['foreignTable'];
        $foreignColumns = $foreignKey['foreignColumns'];
        $options = $foreignKey['options'] ?? [];

        // Optionally validate the name or generate it
        if (empty($name)) {
            $name = 'fk_' . implode('_', $localColumns);
        }

        return new self($name, $localColumns, $foreignTable, $foreignColumns, $options);
    }

    public static function makeMany(array $foreignKeys)
    {
        return array_map(function ($foreignKey) {
            return self::make($foreignKey);
        }, $foreignKeys);
    }

    public function toArray(Type $type)
    {
        return [
            'name' => $this->name,
            'localColumns' => $this->localColumns,
            'foreignTable' => $this->foreignTable,
            'foreignColumns' => $this->foreignColumns,
            'options' => $this->options
        ];
    }
}

<?php

namespace TCG\Voyager\Database\Schema;

use Illuminate\Support\Facades\DB;

class Index // Removed the 'abstract' keyword
{
    public const PRIMARY = 'PRIMARY';
    public const UNIQUE = 'UNIQUE';
    public const INDEX = 'INDEX';

    protected $name;
    protected $columns;
    protected $isUnique;
    protected $isPrimary;
    protected $flags;
    protected $options;

    public function __construct($name, array $columns, $type, $isPrimary = false, $isUnique = false, array $flags = [], array $options = [])
    {
        $this->name = $name;
        $this->columns = $columns;
        $this->isPrimary = $isPrimary;
        $this->isUnique = $isUnique || $isPrimary;
        $this->flags = $flags;
        $this->options = $options;

        $table = $options['table'] ?? null; // Assume 'table' is provided as an option for simplicity
        $this->createIndex($table, $type);
    }

    protected function createIndex($table, $type)
    {
        switch ($type) {
            case self::PRIMARY:
                DB::statement('ALTER TABLE ' . $table . ' ADD PRIMARY KEY (' . implode(',', $this->columns) . ');');
                break;
            case self::UNIQUE:
                DB::statement('CREATE UNIQUE INDEX ' . $this->name . ' ON ' . $table . ' (' . implode(',', $this->columns) . ');');
                break;
            case self::INDEX:
                DB::statement('CREATE INDEX ' . $this->name . ' ON ' . $table . ' (' . implode(',', $this->columns) . ');');
                break;
        }
    }

    public function toArray()
    {
        return [
            'name'        => $this->name,
            'columns'     => $this->columns,
            'type'        => $this->isPrimary ? self::PRIMARY : ($this->isUnique ? self::UNIQUE : self::INDEX),
            'isPrimary'   => $this->isPrimary,
            'isUnique'    => $this->isUnique,
            'isComposite' => count($this->columns) > 1,
            'flags'       => $this->flags,
            'options'     => $this->options,
        ];
    }

    public static function createName(array $columns, $type, $table = null)
    {
        $table = isset($table) ? trim($table) . '_' : '';
        $type = trim($type);
        $name = strtolower($table . implode('_', $columns) . '_' . $type);

        return str_replace(['-', '.'], '_', $name);
    }

    public static function availableTypes()
    {
        return [
            static::PRIMARY,
            static::UNIQUE,
            static::INDEX,
        ];
    }
}

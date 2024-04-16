<?php

namespace TCG\Voyager\Database\Schema;

use Illuminate\Support\Facades\DB;

abstract class Index
{
    public const PRIMARY = 'PRIMARY';
    public const UNIQUE = 'UNIQUE';
    public const INDEX = 'INDEX';

    public static function make(array $index)
    {
        $columns = $index['columns'];
        if (!is_array($columns)) {
            $columns = [$columns];
        }

        $isPrimary = $index['isPrimary'] ?? false;
        $isUnique = $index['isUnique'] ?? false;
        $type = $index['type'] ?? self::INDEX;

        $name = trim($index['name'] ?? '');
        if (empty($name)) {
            $table = $index['table'] ?? null;
            $name = static::createName($columns, $type, $table);
        }

        // We assume a table is always available for index creation
        $table = $index['table'];

        // Create index on the table based on type
        switch ($type) {
            case self::PRIMARY:
                DB::statement('ALTER TABLE ' . $table . ' ADD PRIMARY KEY (' . implode(',', $columns) . ');');
                break;
            case self::UNIQUE:
                DB::statement('CREATE UNIQUE INDEX ' . $name . ' ON ' . $table . ' (' . implode(',', $columns) . ');');
                break;
            case self::INDEX:
                DB::statement('CREATE INDEX ' . $name . ' ON ' . $table . ' (' . implode(',', $columns) . ');');
                break;
        }

        return [
            'name' => $name,
            'columns' => $columns,
            'type' => $type,
            'isPrimary' => $isPrimary,
            'isUnique' => $isUnique,
            // Flags and options are generally not supported directly in SQL statements, handle manually if needed
            'flags' => $index['flags'] ?? [],
            'options' => $index['options'] ?? [],
        ];
    }

    public static function getType($index)
    {
        return $index['type'] ?? self::INDEX;
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

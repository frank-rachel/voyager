<?php

namespace TCG\Voyager\Database\Schema;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SchemaManager
{
    /**
     * List the table names for the current connection.
     */
    public static function listTableNames(): array
    {
        $builder = DB::connection()->getSchemaBuilder();

        return collect($builder->getTables())->pluck('name')->toArray();
    }

    /**
     * Describe the given table using the schema builder.
     */
    public static function describeTable(string $tableName): Collection
    {
        $builder = DB::connection()->getSchemaBuilder();
        $columns = $builder->getColumns($tableName);
        $indexes = collect($builder->getIndexes($tableName));

        return collect($columns)->mapWithKeys(function ($column) use ($indexes) {
            $name = $column['name'];
            $columnIndexes = $indexes->filter(function ($index) use ($name) {
                return in_array($name, $index['columns']);
            });

            $key = null;
            if ($columnIndexes->isNotEmpty()) {
                $index = $columnIndexes->first();
                if (!empty($index['primary'])) {
                    $key = 'PRI';
                } elseif (!empty($index['unique'])) {
                    $key = 'UNI';
                } else {
                    $key = 'MUL';
                }
            }

            return [$name => [
                'field'   => $name,
                'type'    => $column['type_name'] ?? $column['type'] ?? null,
                'indexes' => $columnIndexes->values()->all(),
                'key'     => $key,
                'name'    => $name,
            ]];
        });
    }

    /**
     * List the column names for the given table.
     */
    public static function listTableColumnNames(string $tableName): array
    {
        return collect(DB::connection()->getSchemaBuilder()->getColumns($tableName))
            ->pluck('name')
            ->toArray();
    }
}

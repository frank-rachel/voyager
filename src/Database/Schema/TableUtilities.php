<?php
use Illuminate\Support\Facades\DB;

class TableUtilities {
    // Assuming we are in some utility class

    public static function getColumnDetails($tableName) {
        $columns = DB::select("SELECT column_name, data_type, is_nullable, column_default, character_maximum_length, numeric_precision, numeric_scale 
                                FROM information_schema.columns 
                                WHERE table_schema = 'public' AND table_name = ?", [$tableName]);

        return array_map(function ($column) {
            $options = [
                'nullable' => $column->is_nullable === 'YES',
                'default' => $column->column_default,
                'length' => $column->character_maximum_length,
                'precision' => $column->numeric_precision,
                'scale' => $column->numeric_scale,
                'unsigned' => strpos($column->data_type, 'int') !== false && strpos($column->column_default, 'nextval(') === false
            ];
            $type = self::convertPostgresTypeToGeneric($column->data_type);
            return new Column($column->column_name, $type, $options);
        }, $columns);
    }

    public static function convertPostgresTypeToGeneric($postgresType) {
        $typeMapping = [
            'character varying' => 'varchar',
            'integer' => 'int',
            'timestamp without time zone' => 'timestamp',
            // Add more mappings as needed
        ];
        return $typeMapping[$postgresType] ?? $postgresType;
    }
}

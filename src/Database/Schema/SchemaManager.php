<?php

namespace TCG\Voyager\Database\Schema;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

// Ensure all functionality here uses Laravel's native classes

abstract class SchemaManager
{
    // todo: trim parameters

    // public static function __callStatic($method, $args)
    // {
        // return static::manager()->$method(...$args);
    // }
	
    // public function getDoctrineSchemaManager()
    public static function manager()
    {
        $connection = $this->getDoctrineConnection();

        // Doctrine v2 expects one parameter while v3 expects two. 2nd will be ignored on v2...
        return $this->getDoctrineDriver()->getSchemaManager(
            $connection,
            $connection->getDatabasePlatform()
        );
    }	

    public static function oldmanager()
    // public static function manager()
    {
		// $connection = $this->getDoctrineConnection();
        return DB::connection()->getDoctrineSchemaManager();
    }

    public static function getName() {
		$platform = SchemaManager::getDatabasePlatform();
        $reflection = new \ReflectionClass($platform);
		$shortName = $reflection->getShortName(); // Gets the short class name
		$platformName = ucfirst(strtolower(preg_replace('/Platform$/', '', $shortName)));
		return $platformName;
	}
	
    public static function getDatabaseConnection()
    {
        // return DB::connection()->getDoctrineConnection();
        return DB::connection();
    }

    // public static function getDatabasePlatform(ServerVersionProvider $versionProvider): PostgreSQLPlatform
    // public static function getDatabasePlatform(): PostgreSQLPlatform
    // {
        // return new PostgreSQLPlatform();
    // }
	public static function getDatabasePlatform() {
		return new class extends PostgreSQLPlatform {
			public function getName() {
				// Implement the getName logic here
				// $className = get_class($this);
				$className = 'Postgresql';
				return ucfirst(strtolower(preg_replace('/Platform$/', '', $className)));
			}
		};
	}

    public static function tableExists($table)
    {
        // if (!is_array($table)) {
            // $table = [$table];
        // }

        // return static::manager()->tablesExist($table);
		return Schema::hasTable($table);
    }

    public static function listTables()
    {
        // $tables = [];

        // foreach (static::manager()->listTableNames() as $tableName) {
            // $tables[$tableName] = static::listTableDetails($tableName);
        // }

        // return $tables;
        return Schema::getTables();
    }
	
    public static function listTableNames()
    {
        // $tables = [];

		$tableNames = array_map(function ($table) {
			return $table['name'];
		}, Schema::getTables());
        return $tableNames;
    }

    /**
     * @param string $tableName
     *
     * @return \TCG\Voyager\Database\Schema\Table
     */
	 
    public static function listTableDetails($tableName)
    {
        // Get columns using Laravel's Schema
        $columns = Schema::getColumnListing($tableName);

        // Prepare columns detail array
        $columnDetails = [];
        foreach ($columns as $column) {
            $columnDetails[$column] = Schema::getColumnType($tableName, $column);
        }

        // Fetch foreign keys from information_schema.table_constraints and information_schema.key_column_usage
        $foreignKeys = DB::select("
            SELECT tc.constraint_name, kcu.column_name, ccu.table_name AS foreign_table_name, ccu.column_name AS foreign_column_name
            FROM information_schema.table_constraints AS tc 
            JOIN information_schema.key_column_usage AS kcu 
              ON tc.constraint_name = kcu.constraint_name
            JOIN information_schema.constraint_column_usage AS ccu
              ON ccu.constraint_name = tc.constraint_name
            WHERE tc.table_name = ? AND tc.constraint_type = 'FOREIGN KEY' AND tc.table_schema = ?", 
            [$tableName, env('DB_DATABASE')]);

        // Fetch indexes from pg_indexes
        $indexes = DB::select("
            SELECT indexname, indexdef 
            FROM pg_indexes 
            WHERE tablename = ? AND schemaname = ?", 
            [$tableName, env('DB_DATABASE')]);
        return [
            'tableName' => $tableName,
            'columns' => $columnDetails,
            'indexes' => $indexes,
            'foreignKeys' => $foreignKeys
        ];
    }
// */
/*
    public static function listTableDetails($tableName)
    {
        $columns = static::manager()->listTableColumns($tableName);

        $foreignKeys = [];
        if (static::manager()->getDatabasePlatform()->supportsForeignKeyConstraints()) {
            $foreignKeys = static::manager()->listTableForeignKeys($tableName);
        }

        $indexes = static::manager()->listTableIndexes($tableName);

        return new Table($tableName, $columns, $indexes, [], $foreignKeys, []);
    }
// */
    /**
     * Describes given table.
     *
     * @param string $tableName
     *
     * @return \Illuminate\Support\Collection
     */
    public static function describeTable($tableName)
    {
        Type::registerCustomPlatformTypes();

        $table = static::listTableDetails($tableName);

        return collect($table->columns)->map(function ($column) use ($table) {
            $columnArr = Column::toArray($column);

            $columnArr['field'] = $columnArr['name'];
            $columnArr['type'] = $columnArr['type']['name'];

            // Set the indexes and key
            $columnArr['indexes'] = [];
            $columnArr['key'] = null;
            if ($columnArr['indexes'] = $table->getColumnsIndexes($columnArr['name'], true)) {
                // Convert indexes to Array
                foreach ($columnArr['indexes'] as $name => $index) {
                    $columnArr['indexes'][$name] = Index::toArray($index);
                }

                // If there are multiple indexes for the column
                // the Key will be one with highest priority
                $indexType = array_values($columnArr['indexes'])[0]['type'];
                $columnArr['key'] = substr($indexType, 0, 3);
            }

            return $columnArr;
        });
    }

    public static function dropTable($tableName)
	{
		Schema::dropIfExists($tableName);
	}
	
    public static function listTableColumns($tableName)
    {
        // Type::registerCustomPlatformTypes();

        // $columnNames = [];

        // foreach (static::manager()->listTableColumns($tableName) as $column) {
            // $columnNames[] = $column->getName();
        // }

        // return $columnNames;
        return Schema::getColumns($tableName);
    }

    public static function listTableColumnNames($tableName)
    {
        // $tables = [];

		$ColumnNames = array_map(function ($table) {
			return $table['name'];
		}, Schema::getColumns($tableName));
        return $ColumnNames;
    }

    public static function createTable($table)
	{
		
        $table = Table::make($table);
		return $table;
        // $indexes = [];
        // foreach ($table['indexes'] as $indexArr) {
            // $index = Index::make($indexArr);
            // $indexes[$index->getName()] = $index;
        // }

        // $foreignKeys = [];
        // foreach ($table['foreignKeys'] as $foreignKeyArr) {
            // $foreignKey = ForeignKey::make($foreignKeyArr);
            // $foreignKeys[$foreignKey->getName()] = $foreignKey;
        // }

        // $options = $table['options'];
		
		// Schema::create($name, function (Blueprint $table) {
			// $table->id();
			// $table->string('name');
			// $table->string('email');
			// $table->timestamps();
		// });		
	}
    // public static function createTable($table)
    // {
        // if (!($table instanceof DoctrineTable)) {
            // $table = Table::make($table);
        // }

        // static::manager()->createTable($table);
    // }

    public static function getDoctrineTable($table)
    {
        $table = trim($table);

        if (!static::tableExists($table)) {
            throw SchemaException::tableDoesNotExist($table);
        }

        return static::manager()->listTableDetails($table);
    }

    public static function getDoctrineColumn($table, $column)
    {
        return static::getDoctrineTable($table)->getColumn($column);
    }
}

<?php
namespace TCG\Voyager\Database\Schema;

class Table
{
    public $name;
    public $oldName; // Added to keep track of the original table name
    public $primaryKeyName;
    public $columns = [];
    public $indexes = [];
    public $foreignKeys = [];
    public $options = [];

    public function __construct($name, array $columns = [], array $indexes = [], array $foreignKeys = [], array $options = [])
    {
        $this->name = $name;
        $this->oldName = $name; // Initialize oldName with the current name upon creation
        $this->initializeColumns($columns);
        $this->initializeIndexes($indexes);
        $this->foreignKeys = $foreignKeys;
        $this->options = $options;
    }

	private function initializeColumns(array $columnData)
	{
		foreach ($columnData as $colName => $col) {
			if ($col instanceof Column) {
				$this->addColumn($col);
			} else if (is_array($col)) {
				// Ensure all required data is provided and correctly used
				$this->addColumn(new Column(
					$colName,
					$col['type'], 
					$col['options'] ?? [], 
					$this->name  // Pass the table name if required by your Column class
				));
			} else {
				throw new \InvalidArgumentException("Invalid column data provided for '$colName'");
			}
		}
	}

    public function toArray()
    {
		$columnsArr = [];
		foreach ($this->columns as $column) {
			$columnsArr[] = $column->toArray();
		}

		return [
			'name' => $this->name,
			'oldName' => $this->oldName, // Include oldName in the serialized output
 			'columns' => $columnsArr,
            'indexes'        => $this->exportIndexesToArray(),
            'primaryKeyName' => $this->primaryKeyName,
            'foreignKeys'    => $this->exportForeignKeysToArray(),
            'options'        => $this->options,
        ];
    }


    public function addColumn(Column $column)
    {
        $this->columns[$column->getName()] = $column;
    }


	private function initializeIndexes(array $indexData)
	{
		foreach ($indexData as $index) {
			if ($index instanceof Index) {
				// If $index is already an Index object, simply add it
				$this->addIndex($index);
			} else if (is_array($index)) {
				// If $index is an array, create a new Index object from it
				$this->addIndex(new Index(
					$index['name'],
					$index['columns'],
					$index['type'],
					$index['isPrimary'] ?? false,
					$index['isUnique'] ?? false,
					$index['flags'] ?? [],
					$index['options'] ?? []
				));
			} else {
				// Optionally handle unexpected data types
				throw new \InvalidArgumentException("Invalid index data provided");
			}
		}
	}


    public function addIndex(Index $index)
    {
        $this->indexes[] = $index;
    }


    // public function toArray(Type $type)
    // {
        // return [
            // 'name' => $this->name,
            // 'columns' => array_map(function ($column) { return $column->toArray(); }, $this->columns),
            // 'indexes' => array_map(function ($index) { return $index->toArray(); }, $this->indexes),
            // 'foreignKeys' => $this->foreignKeys,
            // 'options' => $this->options,
        // ];
    // }


    public function removeColumn($columnName)
    {
        foreach ($this->columns as $i => $column) {
            if ($column->getName() === $columnName) {
                unset($this->columns[$i]);
                break;
            }
        }
        $this->columns = array_values($this->columns);  // Re-index the array
    }

    public function getColumn($columnName)
    {
        foreach ($this->columns as $column) {
            if ($column->getName() === $columnName) {
                return $column;
            }
        }
        return null;
    }

    public function toJson()
    {
        return json_encode($this->toArray());
    }

    public function getColumns()
    {
        return $this->columns;
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
            Column::makeMany($table['columns'], $table['name']),
            Index::makeMany($table['indexes'], $table['name']),
            ForeignKey::makeMany($table['foreignKeys'], $table['name']),
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

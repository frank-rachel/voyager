<?php

namespace TCG\Voyager\Database\Schema;

class Table
{
    protected $name;
    protected $columns = [];
    protected $indexes = [];
    protected $foreignKeys = [];
    protected $options = [];

    public function __construct($name, $columns = [], $indexes = [], $foreignKeys = [], $options = [])
    {
        $this->name = $name;
        $this->columns = $columns;
        $this->indexes = $indexes;
        $this->foreignKeys = $foreignKeys;
        $this->options = $options;
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

    public function toArray()
    {
        return [
            'name' => $this->name,
            'columns' => array_map(function($column) { return $column->toArray(); }, $this->columns),
            'indexes' => array_map(function($index) { return $index->toArray(); }, $this->indexes),
            'foreignKeys' => array_map(function($fk) { return $fk->toArray(); }, $this->foreignKeys),
            'options' => $this->options
        ];
    }

    public function toJson()
    {
        return json_encode($this->toArray());
    }

    // Example getters and setters
    public function getName()
    {
        return $this->name;
    }

    // Add other necessary methods and properties...

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
		// Simplified example; you'd need to develop detailed comparison logic
		$differences = [];
		if ($this->name !== $compareTable->name) {
			$differences['name'] = "Different names: {$this->name} vs {$compareTable->name}";
		}
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


	public function getName()
	{
		return $this->name;
	}

}

<?php

namespace TCG\Voyager\Database\Schema;

use TCG\Voyager\Database\Types\Type;
use TCG\Voyager\Database\Types\TypeRegistry;

class Column
{
    public $name;
	public $oldName;
    public $type;
    public $options;
    public $tableName;

	public function __construct($name, $type, array $options = [], $tableName = null)
	{
		$this->name = $name;
		$this->oldName = $name;
		// Check if $type is already an instance of Type, otherwise get it from TypeRegistry
		// $type = ($type instanceof Type) ? $type : Type::getType(trim($type['name']));
		
		// Check if $type is an instance of Type first
		if ($type instanceof Type) {
			$resolvedType = $type;
		} else if (is_array($type) && isset($type['name'])) {
			// Safely access 'name' if $type is an array
			$resolvedType = Type::getType(trim($type['name']));
		} else {
			// Log or handle unexpected $type format
			// echo("Unexpected type format: " . print_r($type, true));
			// throw new \Exception("Unexpected type format encountered.");
			$resolvedType = Type::getType(trim($type));
		}
		
		$type = $resolvedType;
        $this->options = $options;
		$this->tableName = $tableName;

		// Set defaults from options or use default values
		$this->options['nullable'] = $options['nullable'] ?? true;
		$this->options['default'] = $options['default'] ?? null;
		$this->options['length'] = $options['length'] ?? null;
		$this->options['precision'] = $options['precision'] ?? null;
		$this->options['scale'] = $options['scale'] ?? null;
		$this->options['unsigned'] = $options['unsigned'] ?? false;
		$this->options['fixed'] = $options['fixed'] ?? false;
		$this->options['notnull'] = $options['notnull'] ?? !$this->options['nullable'];
	}

	public function toArray(Type $type)
	{
		$columnArr = $column->toArray();
        $columnArr['type'] = Type::toArray($columnArr['type']);
        return [
			'name' => $this->name,
			// 'type' => $this->type->getName(), // Make sure this outputs only the type name
			'type' => $columnArr['type'],
			'oldName' => $this->name,
			'null' => $this->options['nullable'] ? 'YES' : 'NO',
			'default' => $this->options['default'],
			'length' => $this->options['length'],
			'precision' => $this->options['precision'],
			'scale' => $this->options['scale'],
			'unsigned' => $this->getUnsigned(),
			'fixed' => $this->getFixed(),
			'notnull' => $this->getNotnull(),
			'extra' => $this->getExtra(),
			'composite' => false
		];
	}
	
    public static function make(array $column, string $tableName = null)
    {
        $name = $column['name'];
        $type = $column['type'];
        $options = $column['options'] ?? [];
        return new self($name, $type, $options, $tableName);
    }
	
    public static function makeMany(array $columns, string $tableName = null)
    {
        return array_map(function ($column) use ($tableName) {
            return self::make($column, $tableName);
        }, $columns);
    }

    public function getName()
    {
        return $this->name;
    }

    protected function getExtra()
    {
        $extra = '';
        if (isset($this->options['autoincrement']) && $this->options['autoincrement']) {
            $extra = 'auto_increment';
        }
        return $extra;
    }

    public function getUnsigned(): bool
    {
        return $this->options['unsigned'];
    }

    public function getFixed(): bool
    {
        return $this->options['fixed'];
    }

    public function getNotnull(): bool
    {
        return $this->options['notnull'];
    }
}

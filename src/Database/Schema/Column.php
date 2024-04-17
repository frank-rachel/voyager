<?php
namespace TCG\Voyager\Database\Schema;

use TCG\Voyager\Database\Types\Type;

class Column
{
    public $name;
    public $type;
    public $options;
    public $tableName;

	public function __construct($name, $type, array $options = [], $tableName = null)
	{
		$this->name = $name;
		$this->type = Type::getType($type);  // Assume getType returns an instance of Type
		$this->options = $options;
		$this->tableName = $tableName;

		// Set defaults
		$this->options['nullable'] = $options['nullable'] ?? true;
		$this->options['default'] = $options['default'] ?? null;
		$this->options['length'] = $options['length'] ?? null;
		$this->options['precision'] = $options['precision'] ?? null;
		$this->options['scale'] = $options['scale'] ?? null;
		$this->options['unsigned'] = $options['unsigned'] ?? false;
		$this->options['fixed'] = $options['fixed'] ?? false;
		$this->options['notnull'] = $options['notnull'] ?? !$this->options['nullable'];
	}


    public static function make(array $column, string $tableName = null)
    {
        $name = Identifier::validate($column['name'], 'Column');
        $type = Type::getType(trim($column['type']['name']));
        $type->tableName = $tableName;

        $options = array_diff_key($column, array_flip(['name', 'composite', 'oldName', 'null', 'extra', 'type', 'charset', 'collation']));

        return new self($name, $type, $options, $tableName);
    }

	public function toArray()
	{
		// Ensure $this->type is an instance of the Type class
		if (!$this->type instanceof Type) {
			throw new \InvalidArgumentException("Expected Type instance, got " . gettype($this->type));
		}

		$columnArr = [
			'name' => $this->name,
			'type' => $this->type->toArray(), // Call toArray on the instance of Type
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
			'composite' => false  // Assume false or set based on actual logic
		];

		return $columnArr;
	}


    protected function getExtra()
    {
        $extra = '';
        // PostgreSQL does not use auto_increment, example only
        if (!empty($this->options['autoincrement'])) {
            $extra .= 'auto_increment';
        }
        return $extra;
    }

    // Additional getters and setters for the properties
    public function getUnsigned(): bool { return $this->options['unsigned']; }
    public function getFixed(): bool { return $this->options['fixed']; }
    public function getNotnull(): bool { return $this->options['notnull']; }
}

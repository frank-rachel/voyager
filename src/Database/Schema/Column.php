<?php

namespace TCG\Voyager\Database\Schema;

use TCG\Voyager\Database\Types\TypeRegistry;

class Column
{
    public $name;
    public $type;
    public $options;
    public $tableName;

    public function __construct($name, $type, array $options = [], $tableName = null)
    {
        $this->name = $name;
        // Resolve the type name using the TypeRegistry
        $this->type = TypeRegistry::getType($type)->getName();
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

	public function toArray()
	{
		return [
			'name' => $this->name,
			'type' => $this->type->getName(), // Ensure this returns just the type name
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
